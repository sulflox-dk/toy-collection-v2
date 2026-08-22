<?php
namespace App\Modules\Backup\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Core\Config;
use App\Kernel\Database\Database;
use PDO;
use RuntimeException;
use ZipArchive;

class BackupController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $tableCount = (int) $db->fetch(
            "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE()"
        )['c'];

        $mediaPath = rtrim((string) Config::get('app.paths.media_uploads'), '/');
        $mediaStats = $this->mediaDirStats($mediaPath);

        $this->render('backup_index', [
            'title' => 'Backup & Restore',
            'baseUrl' => rtrim(Config::get('app.url', ''), '/') . '/',
            'tableCount' => $tableCount,
            'mediaFileCount' => $mediaStats['count'],
            'mediaSizeFormatted' => $this->formatBytes($mediaStats['bytes']),
            'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            'postMaxSize' => ini_get('post_max_size'),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / (1024 ** $power), 1) . ' ' . $units[$power];
    }

    /**
     * Stream a fresh backup zip (full DB dump + every uploaded media file)
     * straight to the browser as a download.
     * GET /backup/download
     */
    public function download(Request $request): void
    {
        $tmpZip = tempnam(sys_get_temp_dir(), 'toy_collection_backup_');
        if ($tmpZip === false) {
            $this->abort(500, 'Could not create a temporary file for the backup.');
            return;
        }
        // tempnam() creates the file without a .zip extension; ZipArchive
        // doesn't care, but keep the path handling simple either way.

        try {
            $this->buildBackupZip($tmpZip);

            $filename = 'toy-collection-backup-' . date('Y-m-d_His') . '.zip';

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tmpZip));
            header('Cache-Control: no-store');
            readfile($tmpZip);
        } finally {
            @unlink($tmpZip);
        }
    }

    /**
     * AJAX: restore the database and media library from an uploaded backup
     * zip — wipes and fully replaces both. Destructive by design: a backup
     * is a point-in-time snapshot, not something to merge with what's
     * already there.
     * POST /backup/restore
     */
    public function restore(Request $request): void
    {
        if ($request->input('confirm') !== 'yes') {
            $this->json(['error' => 'You must confirm before restoring — this replaces your entire database and media library.'], 400);
            return;
        }

        if (empty($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $message = $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE
                ? 'The backup file is larger than this server currently allows for uploads (see the limits shown above).'
                : 'No backup file was uploaded, or the upload failed.';
            $this->json(['error' => $message], 400);
            return;
        }

        try {
            $manifest = $this->restoreFromZip($_FILES['backup_file']['tmp_name']);
        } catch (\Throwable $e) {
            $this->json([
                'error' => 'Restore failed: ' . $e->getMessage()
                    . ' If this happened partway through, your database may now be in a mixed state — restore again from a known-good backup.',
            ], 500);
            return;
        }

        $this->json(['success' => true, 'manifest' => $manifest]);
    }

    // ---------------------------------------------------------------
    // Backup creation
    // ---------------------------------------------------------------

    private function buildBackupZip(string $zipPath): void
    {
        $pdo = Database::getInstance()->getPdo();
        $dump = $this->dumpDatabase($pdo);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the backup archive.');
        }

        $zip->addFromString('database.json', json_encode($dump, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $mediaPath = rtrim((string) Config::get('app.paths.media_uploads'), '/');
        $mediaFileCount = 0;

        if (is_dir($mediaPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mediaPath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;
                $relative = 'media/' . substr($file->getPathname(), strlen($mediaPath) + 1);
                $zip->addFile($file->getPathname(), $relative);
                $mediaFileCount++;
            }
        }

        $manifest = [
            'app' => 'toy-collection-v2',
            'created_at' => date('c'),
            'tables' => count($dump),
            'media_files' => $mediaFileCount,
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();
    }

    /**
     * A full logical dump: every table's own CREATE TABLE statement (so
     * restore recreates the exact same schema, indexes, and auto-increment
     * position) plus every row, as plain arrays — deliberately not a raw
     * .sql text blob, so restore never has to re-parse SQL back apart.
     */
    private function dumpDatabase(PDO $pdo): array
    {
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $dump = [];
        foreach ($tables as $table) {
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

            $dump[$table] = [
                'create' => $createRow['Create Table'] ?? '',
                'rows' => $rows,
            ];
        }

        return $dump;
    }

    // ---------------------------------------------------------------
    // Restore
    // ---------------------------------------------------------------

    private function restoreFromZip(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Could not open the uploaded file as a zip archive.');
        }

        $manifestJson = $zip->getFromName('manifest.json');
        $dumpJson = $zip->getFromName('database.json');
        if ($manifestJson === false || $dumpJson === false) {
            $zip->close();
            throw new RuntimeException('This doesn\'t look like a valid backup file (missing manifest.json or database.json).');
        }

        $dump = json_decode($dumpJson, true);
        if (!is_array($dump)) {
            $zip->close();
            throw new RuntimeException('The backup\'s database.json is not valid.');
        }

        $this->restoreDatabase(Database::getInstance()->getPdo(), $dump);

        $mediaPath = rtrim((string) Config::get('app.paths.media_uploads'), '/');
        $this->replaceMediaDirectory($mediaPath, $zip);

        $zip->close();

        return json_decode($manifestJson, true) ?: [];
    }

    private function restoreDatabase(PDO $pdo, array $dump): void
    {
        // Fail fast, before touching anything, if the dump is malformed —
        // table/column names only ever come from our own SHOW TABLES /
        // SELECT * in a real backup, this is a corruption/tampering guard,
        // not something a genuine backup file would ever trip.
        foreach (array_keys($dump) as $table) {
            $this->assertSafeIdentifier((string) $table);
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        // A restore has to be able to reproduce exactly what the live DB
        // held at backup time, including any pre-existing data quirks
        // (e.g. an enum column that picked up an empty string somewhere
        // along the way) — strict mode would otherwise reject re-inserting
        // a value the table is already holding just fine. Session-scoped,
        // so it doesn't affect anything past this connection.
        $pdo->exec("SET SESSION sql_mode = ''");

        foreach ($dump as $table => $tableData) {
            $createSql = $tableData['create'] ?? '';
            if ($createSql === '') continue;

            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            $pdo->exec($createSql);

            $rows = $tableData['rows'] ?? [];
            if (empty($rows)) continue;

            $columns = array_keys($rows[0]);
            foreach ($columns as $column) {
                $this->assertSafeIdentifier((string) $column);
            }
            $columnList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $stmt = $pdo->prepare("INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})");

            foreach ($rows as $row) {
                $stmt->execute(array_values($row));
            }
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function assertSafeIdentifier(string $name): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new RuntimeException("Refusing to restore: unexpected table/column name \"{$name}\" in backup file.");
        }
    }

    /**
     * Wipe the current media directory and replace it with the zip's own
     * media/ entries — additive extraction would leave orphaned files from
     * whatever was on disk before the restore.
     */
    private function replaceMediaDirectory(string $mediaPath, ZipArchive $zip): void
    {
        if (is_dir($mediaPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mediaPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
        } else {
            mkdir($mediaPath, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || strpos($name, 'media/') !== 0 || substr($name, -1) === '/') {
                continue;
            }

            $relative = substr($name, strlen('media/'));
            $destPath = $mediaPath . '/' . $relative;
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $contents = $zip->getFromIndex($i);
            if ($contents !== false) {
                file_put_contents($destPath, $contents);
            }
        }
    }

    private function mediaDirStats(string $mediaPath): array
    {
        if (!is_dir($mediaPath)) {
            return ['count' => 0, 'bytes' => 0];
        }

        $count = 0;
        $bytes = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($mediaPath, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
                $bytes += $file->getSize();
            }
        }

        return ['count' => $count, 'bytes' => $bytes];
    }
}
