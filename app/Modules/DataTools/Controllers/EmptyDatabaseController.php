<?php
namespace App\Modules\DataTools\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use PDO;

/**
 * Three cumulative, increasingly-broad "empty the collection" levels,
 * matching the real foreign-key dependency chain (Collection depends on
 * Catalog, which depends on Meta) — each level always includes every level
 * below it, since a lower level's tables reference the ones above.
 */
class EmptyDatabaseController extends Controller
{
    private const TABLES_BY_LEVEL = [
        1 => ['collection_toy_items', 'collection_toys', 'collection_storage_units', 'collection_sources'],
        2 => ['importer_logs', 'importer_items', 'catalog_toy_items', 'catalog_toys'],
        3 => [
            'meta_subjects', 'meta_entertainment_sources', 'meta_toy_lines', 'meta_product_types',
            'meta_manufacturers', 'meta_universes', 'meta_acquisition_statuses', 'meta_condition_grades',
            'meta_grading_companies', 'meta_grader_tiers', 'meta_packaging_types',
        ],
    ];

    private const MEDIA_ENTITY_TYPES_BY_LEVEL = [
        1 => ['collection_toys', 'collection_toy_items', 'sources'],
        2 => ['catalog_toys', 'catalog_toy_items'],
        3 => ['universes', 'manufacturers', 'toy_lines', 'entertainment_sources'],
    ];

    private const TABLE_LABELS = [
        'collection_toy_items' => 'Collection item details',
        'collection_toys' => 'Collection (your owned toys)',
        'collection_storage_units' => 'Storage units',
        'collection_sources' => 'Purchase sources',
        'importer_logs' => 'Import logs',
        'importer_items' => 'Import history',
        'catalog_toy_items' => 'Catalog included-items',
        'catalog_toys' => 'Catalog toys',
        'meta_subjects' => 'Subjects (characters/accessories)',
        'meta_entertainment_sources' => 'Entertainment sources',
        'meta_toy_lines' => 'Toy lines',
        'meta_product_types' => 'Product types',
        'meta_manufacturers' => 'Manufacturers',
        'meta_universes' => 'Universes',
        'meta_acquisition_statuses' => 'Acquisition statuses',
        'meta_condition_grades' => 'Condition grades',
        'meta_grading_companies' => 'Grading companies',
        'meta_grader_tiers' => 'Grader tiers',
        'meta_packaging_types' => 'Packaging types',
    ];

    private const LEVEL_LABELS = [
        1 => 'Collection',
        2 => 'Catalog',
        3 => 'Meta Data',
    ];

    public function index(Request $request): void
    {
        $db = Database::getInstance();

        $counts = [];
        foreach (self::TABLES_BY_LEVEL as $level => $tables) {
            foreach ($tables as $table) {
                $counts[$level][$table] = (int) $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            }
        }

        $mediaCounts = [];
        foreach (self::MEDIA_ENTITY_TYPES_BY_LEVEL as $level => $types) {
            $mediaCounts[$level] = $this->countMediaFor($db, $types);
        }

        $this->render('empty_database_index', [
            'title' => 'Empty Database',
            'baseUrl' => rtrim(\App\Kernel\Core\Config::get('app.url', ''), '/') . '/',
            'counts' => $counts,
            'mediaCounts' => $mediaCounts,
            'tableLabels' => self::TABLE_LABELS,
            'levelLabels' => self::LEVEL_LABELS,
        ]);
    }

    /**
     * AJAX: wipe every table (and associated images) in this level and every
     * level below it. Always cumulative — Level 2 always includes Level 1,
     * Level 3 always includes Level 1 and 2, since the lower levels'
     * tables have foreign keys into what the higher levels would leave
     * standing.
     * POST /empty-database/level/{level}
     */
    public function empty(Request $request, int $level): void
    {
        if (!isset(self::TABLES_BY_LEVEL[$level])) {
            $this->json(['error' => 'Invalid level.'], 400);
            return;
        }

        if ($request->input('confirm') !== 'EMPTY') {
            $this->json(['error' => 'Type EMPTY to confirm.'], 400);
            return;
        }

        $tables = [];
        $mediaEntityTypes = [];
        for ($i = 1; $i <= $level; $i++) {
            $tables = array_merge($tables, self::TABLES_BY_LEVEL[$i]);
            $mediaEntityTypes = array_merge($mediaEntityTypes, self::MEDIA_ENTITY_TYPES_BY_LEVEL[$i]);
        }

        $db = Database::getInstance();
        $pdo = $db->getPdo();

        $mediaResult = $this->deleteMediaFor($db, $mediaEntityTypes);

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE `{$table}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->json([
            'success' => true,
            'level' => $level,
            'tablesEmptied' => count($tables),
            'filesDeleted' => $mediaResult['filesDeleted'],
            'linksRemoved' => $mediaResult['linksRemoved'],
        ]);
    }

    private function countMediaFor(Database $db, array $entityTypes): int
    {
        $placeholders = implode(',', array_fill(0, count($entityTypes), '?'));
        return (int) $db->query(
            "SELECT COUNT(DISTINCT media_file_id) FROM media_links WHERE entity_type IN ({$placeholders})",
            $entityTypes
        )->fetchColumn();
    }

    /**
     * Remove every media_links row for the given entity types, then delete
     * the underlying media_files (and their file on disk) for any of them
     * that's now completely unlinked — but only those. A media file can be
     * shared across entity types (the picker's "Search Library" tab links
     * an existing file rather than re-uploading), so one still linked to
     * something outside this wipe is left alone.
     */
    private function deleteMediaFor(Database $db, array $entityTypes): array
    {
        if (empty($entityTypes)) {
            return ['filesDeleted' => 0, 'linksRemoved' => 0];
        }

        $placeholders = implode(',', array_fill(0, count($entityTypes), '?'));

        $mediaFileIds = $db->query(
            "SELECT DISTINCT media_file_id FROM media_links WHERE entity_type IN ({$placeholders})",
            $entityTypes
        )->fetchAll(PDO::FETCH_COLUMN);

        $linksRemoved = $db->execute(
            "DELETE FROM media_links WHERE entity_type IN ({$placeholders})",
            $entityTypes
        );

        $filesDeleted = 0;
        if (!empty($mediaFileIds)) {
            $uploadsRoot = ROOT_PATH . '/public/';

            foreach ($mediaFileIds as $mediaFileId) {
                $stillLinked = (int) $db->query(
                    "SELECT COUNT(*) FROM media_links WHERE media_file_id = ?",
                    [$mediaFileId]
                )->fetchColumn();
                if ($stillLinked > 0) continue;

                $file = $db->fetch("SELECT filepath FROM media_files WHERE id = ?", [$mediaFileId]);
                if ($file) {
                    $fullPath = $uploadsRoot . ltrim($file['filepath'], '/');
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }

                $db->execute("DELETE FROM media_file_tags WHERE media_file_id = ?", [$mediaFileId]);
                $db->execute("DELETE FROM media_files WHERE id = ?", [$mediaFileId]);
                $filesDeleted++;
            }
        }

        return ['filesDeleted' => $filesDeleted, 'linksRemoved' => $linksRemoved];
    }
}
