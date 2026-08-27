<?php
namespace App\Modules\Media\Controllers;

use App\Kernel\Database\Database;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Core\Config;
use App\Modules\Media\Models\MediaFile;
use App\Modules\Media\Models\MediaTag;

class MediaFileController extends Controller
{
    private const ALLOWED_ENTITY_TYPES = [
        'catalog_toys',
        'catalog_toy_items',
        'collection_toys',
        'collection_toy_items',
        'universes',
        'manufacturers',
        'toy_lines',
        'entertainment_sources',
        'sources',
        'subjects',
    ];
    public function index(Request $request): void
    {
        $tags = MediaTag::getAll();

        $this->render('media_file_index', [
            'title' => 'Media Library',
            'tags' => $tags,
            'scripts' => [
                'assets/js/modules/media/media_files.js'
            ]
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $search = trim($request->input('q', ''));
        $attachmentType = trim($request->input('attachment_type', ''));
        $tagId = trim($request->input('tag_id', '')); // <-- NY TAG FILTER

        // Send tagId med som 5. parameter
        $data = MediaFile::getPaginated($page, 24, $search, $attachmentType, $tagId);

        $baseUrl = Config::get('app.url');
        $baseUrl = rtrim($baseUrl, '/') . '/';

        $this->renderPartial('media_file_grid', [
            'files' => $data['items'],
            'baseUrl' => $baseUrl,
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    public function store(Request $request): void
    {
        if (!empty($_FILES['file']['name'])) {
            $this->handleUpload($request);
            return;
        }

        $this->json(['error' => 'No file uploaded'], 400);
    }

    private function handleUpload(Request $request): void
    {
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            ];

            $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
            $this->json(['error' => $errorMessage], 400);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        
        if (!in_array($ext, $allowed)) {
            $this->json(['error' => 'Invalid file type.'], 422);
            return;
        }

        // MIME type verification (defense-in-depth beyond extension check)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($detectedMime, $allowedMimes)) {
            $this->json(['error' => 'File content does not match an allowed type.'], 422);
            return;
        }

        // 1. Get Path from Config
        $uploadPath = Config::get('app.paths.media_uploads');
        if (!$uploadPath) {
            $this->json(['error' => 'Upload path not configured.'], 500);
            return;
        }
        
        // Ensure trailing slash
        $uploadPath = rtrim($uploadPath, '/') . '/';

        // 2. Generate Filename (cryptographically random)
        $hashName = bin2hex(random_bytes(16)) . '.' . $ext;
        
        // 3. Create Directory if needed
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // 4. Determine "Web Path" for DB (Relative to public/)
        $publicRoot = ROOT_PATH . '/public/';
        $webPath = str_replace($publicRoot, '', $uploadPath);
        
        // Fallback if path isn't inside public
        if ($webPath === $uploadPath) { 
             $webPath = 'uploads/media/'; 
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $hashName)) {
            // GEM DATABASE-POSTEN OG GEM DET NYE ID
            $newMediaId = MediaFile::create([
                'filename' => $hashName,
                'original_name' => $file['name'],
                'filepath' => $webPath . $hashName,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                'alt_text' => pathinfo($file['name'], PATHINFO_FILENAME)
            ]);
            
            // NYT: TILKNYT TAGS TIL DEN NYE FIL
            $tagIdsStr = $request->input('tag_ids', '');
            if ($tagIdsStr !== '') {
                $tagIdsArray = array_map('intval', explode(',', $tagIdsStr));
                MediaTag::syncForFile($newMediaId, $tagIdsArray);
            }
            
            // --- NYT: AUTOMATIC ENTITY LINKING ---
            $entityType = trim($request->input('entity_type', ''));
            $entityId = (int)$request->input('entity_id', 0);

            if ($entityType !== '' && $entityId > 0 && in_array($entityType, self::ALLOWED_ENTITY_TYPES, true)) {
                MediaFile::linkToEntity($newMediaId, $entityType, $entityId);
            }
            // -------------------------------------
            
            $this->json(['success' => true]);
        } else {
            $this->json(['error' => 'Failed to move uploaded file.'], 500);
        }
    }

    public function update(Request $request, int $id): void
    {
        if (!MediaFile::find($id)) {
            $this->json(['error' => 'File not found'], 404);
            return;
        }

        $title = trim($request->input('title', ''));
        $altText = trim($request->input('alt_text', ''));

        if (mb_strlen($title) > 255) {
            $this->json(['field' => 'title', 'message' => 'Title cannot exceed 255 characters'], 422);
            return;
        }
        if (mb_strlen($altText) > 255) {
            $this->json(['field' => 'alt_text', 'message' => 'Alt text cannot exceed 255 characters'], 422);
            return;
        }

        MediaFile::update($id, [
            'title' => $title,
            'description' => trim($request->input('description', '')),
            'alt_text' => $altText
        ]);

        // NYT: Sync Tags
        $tagIdsStr = $request->input('tag_ids', '');
        $tagIdsArray = $tagIdsStr !== '' ? array_map('intval', explode(',', $tagIdsStr)) : [];
        MediaTag::syncForFile($id, $tagIdsArray);

        $this->json(['success' => true]);
    }

    public function destroy(Request $request, int $id): void
    {
        $file = MediaFile::find($id);
        if (!$file) {
            $this->json(['error' => 'File not found'], 404);
            return;
        }

        // 1. Check for dependencies (Is this file attached to any Universes, Toys, etc.?)
        $linkCount = MediaFile::getUsageCount($id);
        
        // 2. Validate Migration Request
        $migrateTo = (int) $request->input('migrate_to', 0);

        if ($migrateTo > 0) {
            if ($migrateTo === $id) {
                $this->json(['error' => 'Cannot migrate links to the file being deleted.'], 400);
                return;
            }
            $target = MediaFile::find($migrateTo);
            if (!$target) {
                $this->json(['error' => 'The selected target file does not exist.'], 400);
                return;
            }
        }

        // 3. Handle Migration Requirement (409 Conflict)
        if ($linkCount > 0 && $migrateTo === 0) {
            $this->json([
                'requires_migration' => true,
                'message' => "This file is attached to {$linkCount} item(s). Please replace it with another file before deleting.",
                'options_url' => "media-file/migrate-on-delete-options?exclude={$id}"
            ], 409);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 4. Migrate items if requested
            if ($linkCount > 0 && $migrateTo > 0) {
                MediaFile::migrateLinks($id, $migrateTo);
            }

            // 5. Delete physical file (with path traversal guard)
            $this->deletePhysicalFile($file['filepath']);

            // 6. Delete DB record (ON DELETE CASCADE will handle media_file_tags)
            MediaFile::delete($id);

            $db->commit();
            $this->json(['success' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete record. Please try again.'], 500);
        }
    }

    /**
     * Permanently delete every media file with zero attachments — the
     * "No Attachments (Orphans)" set. Nothing here is still in use
     * anywhere, so there's no migration step to worry about, unlike the
     * single-file destroy() above.
     * DELETE /media-file/orphans
     */
    public function destroyOrphans(Request $request): void
    {
        $orphans = MediaFile::getOrphaned();

        if (empty($orphans)) {
            $this->json(['success' => true, 'count' => 0]);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            foreach ($orphans as $file) {
                $this->deletePhysicalFile($file['filepath']);
            }

            $ids = array_column($orphans, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->execute("DELETE FROM media_files WHERE id IN ($placeholders)", $ids);

            $db->commit();
            $this->json(['success' => true, 'count' => count($orphans)]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log('Delete orphans failed: ' . $e->getMessage());
            $this->json(['error' => 'Failed to delete orphaned files. Please try again.'], 500);
        }
    }

    /**
     * Delete an uploaded file's physical copy from disk, refusing to touch
     * anything outside the uploads directory (defends against a filepath
     * value containing '../' somehow reaching here).
     */
    private function deletePhysicalFile(string $filepath): void
    {
        $fullPath = ROOT_PATH . '/public/' . $filepath;
        $realPath = realpath($fullPath);
        $uploadsDir = realpath(ROOT_PATH . '/public/uploads');

        if ($realPath && $uploadsDir && str_starts_with($realPath, $uploadsDir)) {
            unlink($realPath);
        }
    }

    public function migrateOnDeleteOptions(Request $request): void
    {
        $exclude = (int) $request->input('exclude', 0);
        $db = Database::getInstance();
        
        // We concatenate the Title and Filename so the dropdown makes it easy to identify the target file
        $sql = "SELECT id, CONCAT(COALESCE(title, 'Untitled'), ' (', filename, ')') as name FROM media_files";
        $params = [];
        
        if ($exclude > 0) {
            $sql .= " WHERE id != ?";
            $params[] = $exclude;
        }
        
        $sql .= " ORDER BY title ASC, filename ASC";
        
        $options = $db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
        $this->json($options);
    }

    public function searchJson(Request $request): void
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 2) {
            $this->json([]);
            return;
        }
        
        $this->json(MediaFile::searchSimple($q));
    }

    public function link(Request $request): void
    {
        $mediaFileId = (int)$request->input('media_file_id');
        $entityType = trim($request->input('entity_type'));
        $entityId = (int)$request->input('entity_id');

        if (!$mediaFileId || !$entityType || !$entityId) {
            $this->json(['error' => 'Missing data'], 400);
            return;
        }

        if (!in_array($entityType, self::ALLOWED_ENTITY_TYPES, true)) {
            $this->json(['error' => 'Invalid entity type'], 400);
            return;
        }

        MediaFile::linkToEntity($mediaFileId, $entityType, $entityId);
        $this->json(['success' => true]);
    }

    public function getThumbnails(Request $request): void
    {
        $type = trim($request->input('type'));
        $id = (int)$request->input('id');

        if (!$type || !$id) {
            $this->json([]);
            return;
        }

        $this->json(MediaFile::getForEntity($type, $id));
    }

    public function unlink(Request $request): void
    {
        $linkId = (int)$request->input('link_id');
        if ($linkId) {
            $db = Database::getInstance();
            // This deletes the link, NOT the physical media file
            $db->query("DELETE FROM media_links WHERE id = ?", [$linkId]);
        }
        $this->json(['success' => true]);
    }

    /**
     * Mark one media_links row as the featured (primary) image for its
     * entity, unsetting the flag on every other link for that same
     * entity — is_featured is meaningful per (entity_type, entity_id),
     * not per media file, since the same file could in principle be
     * linked to more than one entity.
     * POST /media-file/set-featured
     */
    public function setFeatured(Request $request): void
    {
        $linkId = (int) $request->input('link_id');
        if (!$linkId) {
            $this->json(['error' => 'Missing link_id'], 400);
            return;
        }

        $db = Database::getInstance();
        $link = $db->fetch("SELECT entity_type, entity_id FROM media_links WHERE id = ?", [$linkId]);
        if (!$link) {
            $this->json(['error' => 'Link not found'], 404);
            return;
        }

        $db->execute(
            "UPDATE media_links SET is_featured = 0 WHERE entity_type = ? AND entity_id = ?",
            [$link['entity_type'], $link['entity_id']]
        );
        $db->execute("UPDATE media_links SET is_featured = 1 WHERE id = ?", [$linkId]);

        $this->json(['success' => true]);
    }
}