<?php
namespace App\Modules\Media\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Core\Config;
use App\Modules\Media\Models\MediaFile;
use App\Modules\Media\Models\MediaTag;

class MediaFileController extends Controller
{
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
        finfo_close($finfo);

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

        // Delete physical file (with path traversal guard)
        $fullPath = ROOT_PATH . '/public/' . $file['filepath'];
        $realPath = realpath($fullPath);
        $uploadsDir = realpath(ROOT_PATH . '/public/uploads');

        if ($realPath && $uploadsDir && str_starts_with($realPath, $uploadsDir)) {
            unlink($realPath);
        }

        MediaFile::delete($id);

        $this->json(['success' => true]);
    }
}