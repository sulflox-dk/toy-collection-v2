<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Core\Config;
use App\Kernel\Database\Database;
use App\Modules\Importer\Models\ImporterSource;
use App\Modules\Importer\Models\ImporterItem;
use App\Modules\Importer\Models\ImporterLog;
use App\Modules\Importer\Drivers\SiteDriverInterface;
use App\Modules\Meta\Models\Universe;
use App\Modules\Meta\Models\Manufacturer;
use App\Modules\Meta\Models\ProductType;
use App\Modules\Meta\Models\EntertainmentSource;

class ImporterRunController extends Controller
{
    /** Columns on catalog_toys an import is allowed to write. */
    private const WRITABLE_FIELDS = [
        'name', 'year_released', 'wave', 'assortment_sku', 'upc', 'description',
        'universe_id', 'manufacturer_id', 'toy_line_id',
        'product_type_id', 'entertainment_source_id',
    ];

    public function index(Request $request): void
    {
        $stats = ImporterSource::getStats();
        $db = Database::getInstance();

        // Toy lines joined with manufacturer/universe names, so the dropdown
        // label can disambiguate lines that share a name across universes.
        $toyLines = $db->fetchAll("
            SELECT tl.id, tl.name, m.name AS manufacturer_name, u.name AS universe_name
            FROM meta_toy_lines tl
            LEFT JOIN meta_manufacturers m ON tl.manufacturer_id = m.id
            LEFT JOIN meta_universes u ON tl.universe_id = u.id
            ORDER BY tl.name ASC
        ");

        $activeSourceCount = (int) $db->query(
            "SELECT COUNT(*) FROM importer_sources WHERE is_active = 1"
        )->fetchColumn();

        $this->render('importer_run_index', [
            'title'   => 'Run Import',
            'stats'   => $stats,
            'universes' => Universe::all(),
            'manufacturers' => Manufacturer::all(),
            'toyLines' => $toyLines,
            'productTypes' => ProductType::all(),
            'entertainmentSources' => EntertainmentSource::all(),
            // A single toy can't realistically have more contributing
            // sources than there are active drivers to scrape it from.
            'maxSourcesPerGroup' => max(1, $activeSourceCount),
            'scripts' => [
                'assets/js/modules/importer/importer_run.js'
            ]
        ]);
    }

    /**
     * AJAX: Analyze ONE url and return its scraped result(s).
     * A single detail-page URL returns exactly one result; an overview/
     * listing-page URL returns up to 20 (from the given offset) — this is
     * what powers both "add a source to a toy" and bulk discovery.
     * POST /importer-run/analyze-url
     */
    public function analyzeUrl(Request $request): void
    {
        $url = trim($request->input('url', ''));
        $offset = max(0, (int) $request->input('offset', 0));

        $batchUniverseId = (int) $request->input('universe_id', 0) ?: null;
        $batchManufacturerId = (int) $request->input('manufacturer_id', 0) ?: null;
        $batchToyLineId = (int) $request->input('toy_line_id', 0) ?: null;
        $batchProductTypeId = (int) $request->input('product_type_id', 0) ?: null;
        $batchEntertainmentSourceId = (int) $request->input('entertainment_source_id', 0) ?: null;

        if ($url === '') {
            $this->json(['error' => 'Please enter a URL'], 400);
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json(['error' => 'Invalid URL format'], 400);
            return;
        }

        $source = ImporterSource::findByUrl($url);
        if (!$source) {
            $this->json(['error' => 'No import source matches this URL. Add the source first under Import Sources.'], 404);
            return;
        }

        try {
            $driverClass = $source['driver_class'];
            if (!class_exists($driverClass)) {
                $this->json(['error' => "Driver class not found: $driverClass"], 500);
                return;
            }

            /** @var SiteDriverInterface $driver */
            $driver = new $driverClass();

            $toysToProcess = [];
            $totalFound = null;
            $isOverview = $driver->isOverviewPage($url);

            if ($isOverview) {
                $detailUrls = $driver->parseOverviewPage($url);
                $totalFound = count($detailUrls);
                $pageUrls = array_slice($detailUrls, $offset, 20);

                foreach ($pageUrls as $detailUrl) {
                    try {
                        $toysToProcess[] = $driver->parseSinglePage($detailUrl);
                    } catch (\Exception $e) {
                        continue; // skip individual failures, keep the rest
                    }
                }
            } else {
                $toysToProcess[] = $driver->parseSinglePage($url);
            }

            $db = Database::getInstance();
            $results = [];

            foreach ($toysToProcess as $dto) {
                $item = $dto->toArray();

                $linkedItem = ImporterItem::findByExternal((int) $source['id'], $dto->externalId);

                if ($linkedItem) {
                    $item['status'] = 'linked';
                    $item['existingId'] = (int) $linkedItem['catalog_toy_id'];
                    $item['matchReason'] = 'External ID Match';
                } else {
                    $existingToy = $db->fetch("SELECT id, name FROM catalog_toys WHERE name = ? LIMIT 1", [$dto->name]);
                    if ($existingToy) {
                        $item['status'] = 'conflict';
                        $item['existingId'] = (int) $existingToy['id'];
                        $item['matchReason'] = 'Name Match';
                    } else {
                        $item['status'] = 'new';
                        $item['existingId'] = null;
                    }
                }

                // Batch defaults are a fallback, not an override: whatever the
                // page itself tells us (matched by exact name) always wins,
                // since it's more specific than a blanket setting for the
                // whole session. Universe/product type/entertainment source
                // are never scraped from any site, so the batch default (if
                // any) is simply the only value there is for those.
                $item['universe_id'] = $batchUniverseId;
                $item['product_type_id'] = $batchProductTypeId;
                $item['entertainment_source_id'] = $batchEntertainmentSourceId;

                $item['manufacturer_id'] = null;
                if (!empty($item['manufacturer'])) {
                    $mfg = $db->fetch("SELECT id FROM meta_manufacturers WHERE name = ? LIMIT 1", [$item['manufacturer']]);
                    if ($mfg) $item['manufacturer_id'] = (int) $mfg['id'];
                }
                if (!$item['manufacturer_id'] && $batchManufacturerId) {
                    $item['manufacturer_id'] = $batchManufacturerId;
                }

                $item['toy_line_id'] = null;
                if (!empty($item['toyLine'])) {
                    $tl = $db->fetch("SELECT id FROM meta_toy_lines WHERE name = ? LIMIT 1", [$item['toyLine']]);
                    if ($tl) $item['toy_line_id'] = (int) $tl['id'];
                }
                if (!$item['toy_line_id'] && $batchToyLineId) {
                    $item['toy_line_id'] = $batchToyLineId;
                }
                $item['source_id'] = (int) $source['id'];
                $item['source_name'] = $source['name'];
                $results[] = $item;
            }

            $this->json([
                'success' => true,
                'data' => $results,
                'source' => $source['name'],
                'isOverview' => $isOverview,
                'offset' => $offset,
                'totalFound' => $totalFound,
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Scraping failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: search existing catalog toys, for "attach this import to an
     * existing toy" instead of relying on the automatic name match.
     * GET /importer-run/search-catalog?q=...
     */
    public function searchCatalog(Request $request): void
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 2) {
            $this->json(['data' => []]);
            return;
        }

        $db = Database::getInstance();
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';

        $rows = $db->query("
            SELECT cat.id, cat.name, cat.year_released,
                   u.name AS universe_name, m.name AS manufacturer_name,
                   (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                    WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id
                    ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS image_path
            FROM catalog_toys cat
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_manufacturers m ON cat.manufacturer_id = m.id
            WHERE cat.deleted_at IS NULL AND cat.name LIKE ?
            ORDER BY cat.name ASC
            LIMIT 15
        ", [$baseUrl, "%$q%"])->fetchAll(\PDO::FETCH_ASSOC);

        $this->json(['data' => $rows]);
    }

    /**
     * AJAX: current field values (+ what it already has) for an existing
     * catalog toy, to build the "current vs scraped" compare grid.
     * GET /importer-run/catalog-toy/{id}
     */
    public function getCatalogToy(Request $request, int $id): void
    {
        $db = Database::getInstance();

        $toy = $db->fetch("SELECT * FROM catalog_toys WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$toy) {
            $this->json(['error' => 'Catalog toy not found'], 404);
            return;
        }

        $accessoryNames = $db->query("
            SELECT s.name FROM catalog_toy_items cti
            JOIN meta_subjects s ON cti.subject_id = s.id
            WHERE cti.catalog_toy_id = ?
        ", [$id])->fetchAll(\PDO::FETCH_COLUMN);

        $imageCount = (int) $db->query(
            "SELECT COUNT(*) FROM media_links WHERE entity_type = 'catalog_toys' AND entity_id = ?",
            [$id]
        )->fetchColumn();

        $this->json([
            'success' => true,
            'toy' => $toy,
            'existingAccessories' => $accessoryNames,
            'existingImageCount' => $imageCount,
        ]);
    }

    /**
     * AJAX: execute the import for a batch of resolved toy groups.
     * POST /importer-run/import
     */
    public function runImport(Request $request): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $groups = $data['groups'] ?? [];

        if (empty($groups)) {
            $this->json(['error' => 'No items selected'], 400);
            return;
        }

        $db = Database::getInstance();
        $successCount = 0;
        $errors = [];

        foreach ($groups as $group) {
            $inTransaction = false;
            $name = $group['fields']['name'] ?? 'Unknown';

            try {
                $mode = $group['mode'] ?? 'create';
                $fields = is_array($group['fields'] ?? null) ? $group['fields'] : [];
                $accessories = is_array($group['accessories'] ?? null) ? $group['accessories'] : [];
                $images = is_array($group['images'] ?? null) ? $group['images'] : [];
                $sources = is_array($group['sources'] ?? null) ? $group['sources'] : [];

                if (empty($sources)) {
                    throw new \RuntimeException('No source URLs on this item');
                }

                $db->beginTransaction();
                $inTransaction = true;

                if ($mode === 'update') {
                    $catalogToyId = (int) ($group['targetCatalogToyId'] ?? 0);
                    if (!$catalogToyId || !$db->fetch("SELECT id FROM catalog_toys WHERE id = ?", [$catalogToyId])) {
                        throw new \RuntimeException('Target catalog toy not found');
                    }

                    $setClauses = [];
                    $params = [];
                    foreach ($fields as $col => $val) {
                        if (!in_array($col, self::WRITABLE_FIELDS, true)) continue;
                        $setClauses[] = "`{$col}` = ?";
                        $params[] = ($val === '' ? null : $val);
                    }

                    if (!empty($setClauses)) {
                        $params[] = $catalogToyId;
                        $db->execute(
                            "UPDATE catalog_toys SET " . implode(', ', $setClauses) . " WHERE id = ?",
                            $params
                        );
                    }
                } else {
                    if (empty($fields['name'])) {
                        throw new \RuntimeException('Name is required');
                    }
                    if (empty($fields['toy_line_id'])) {
                        throw new \RuntimeException('Toy Line is required');
                    }

                    $name = trim($fields['name']);
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . '-' . time() . '-' . mt_rand(100, 999);

                    $columns = ['name', 'slug'];
                    $placeholders = ['?', '?'];
                    $params = [$name, $slug];

                    foreach (self::WRITABLE_FIELDS as $col) {
                        if ($col === 'name') continue;
                        $columns[] = $col;
                        $placeholders[] = '?';
                        $val = $fields[$col] ?? null;
                        $params[] = ($val === '' ? null : $val);
                    }

                    $db->execute(
                        "INSERT INTO catalog_toys (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")",
                        $params
                    );
                    $catalogToyId = $db->lastInsertId();
                }

                $catalogUniverseId = $db->fetch("SELECT universe_id FROM catalog_toys WHERE id = ?", [$catalogToyId])['universe_id'] ?? null;
                $this->addAccessories($db, $catalogToyId, $accessories, $catalogUniverseId ? (int) $catalogUniverseId : null);
                $this->addImages($db, $catalogToyId, $images);

                $importItemIds = [];
                foreach ($sources as $src) {
                    $sourceId = (int) ($src['source_id'] ?? 0);
                    $externalId = $src['externalId'] ?? '';
                    $externalUrl = $src['externalUrl'] ?? '';
                    if (!$sourceId || $externalId === '') continue;

                    $importItemIds[] = ImporterItem::registerImport($sourceId, $catalogToyId, $externalId, $externalUrl);
                }

                $db->commit();
                $inTransaction = false;

                $primarySourceId = (int) ($sources[0]['source_id'] ?? 0);
                $logMessage = $mode === 'update'
                    ? "Updated: {$name} (from " . count($sources) . " source(s))"
                    : "Imported: {$name} (from " . count($sources) . " source(s))";
                ImporterLog::log($primarySourceId, 'Success', $importItemIds[0] ?? null, $logMessage);

                $successCount++;

            } catch (\Exception $e) {
                if ($inTransaction) {
                    $db->rollBack();
                }

                $errorMsg = "Failed: {$name} - " . $e->getMessage();
                $errors[] = $errorMsg;

                $primarySourceId = (int) ($group['sources'][0]['source_id'] ?? 0);
                if ($primarySourceId) {
                    ImporterLog::log($primarySourceId, 'Error', null, $errorMsg);
                }
            }
        }

        $this->json([
            'success' => true,
            'count' => $successCount,
            'errors' => $errors
        ]);
    }

    /**
     * Find-or-create a meta_subjects row per accessory name and attach it to
     * the toy — skipping any name the toy already has, so this is safe to
     * call for both brand-new toys and top-ups of existing ones.
     *
     * A newly-created subject is stamped with the toy's own universe_id —
     * the catalog wizard's subject dropdown filters by universe, so a
     * subject left without one would be invisible to it (and get silently
     * un-selected) even though its catalog_toy_items row is correct. A
     * matched existing subject that's missing its universe_id is backfilled
     * the same way, so legacy rows self-heal the next time they're touched.
     */
    private function addAccessories(Database $db, int $catalogToyId, array $accessoryNames, ?int $universeId = null): void
    {
        if (empty($accessoryNames)) return;

        $existing = $db->query("
            SELECT s.name FROM catalog_toy_items cti
            JOIN meta_subjects s ON cti.subject_id = s.id
            WHERE cti.catalog_toy_id = ?
        ", [$catalogToyId])->fetchAll(\PDO::FETCH_COLUMN);
        $existingLower = array_map('mb_strtolower', $existing);

        foreach (array_unique($accessoryNames) as $accessoryName) {
            $accessoryName = trim($accessoryName);
            if ($accessoryName === '' || in_array(mb_strtolower($accessoryName), $existingLower, true)) {
                continue;
            }

            $subject = $db->fetch("SELECT id, universe_id FROM meta_subjects WHERE name = ? LIMIT 1", [$accessoryName]);

            if ($subject) {
                $subjectId = (int) $subject['id'];
                if ($universeId && !$subject['universe_id']) {
                    $db->execute("UPDATE meta_subjects SET universe_id = ? WHERE id = ?", [$universeId, $subjectId]);
                }
            } else {
                $subjectSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $accessoryName), '-'))
                    . '-' . time() . '-' . mt_rand(100, 999);

                $db->execute(
                    "INSERT INTO meta_subjects (name, slug, type, universe_id) VALUES (?, ?, 'Accessory', ?)",
                    [$accessoryName, $subjectSlug, $universeId]
                );
                $subjectId = $db->lastInsertId();
            }

            $db->execute(
                "INSERT INTO catalog_toy_items (catalog_toy_id, subject_id, description) VALUES (?, ?, NULL)",
                [$catalogToyId, $subjectId]
            );
        }
    }

    /**
     * Download every scraped image URL and attach it to the toy. Always
     * additive — existing photos are never touched or replaced, and (per
     * product decision) no attempt is made to detect a duplicate of an
     * already-imported image, since nothing tracks which image came from
     * which URL.
     */
    private function addImages(Database $db, int $catalogToyId, array $imageUrls): void
    {
        if (empty($imageUrls)) return;

        $uploadPath = rtrim((string) Config::get('app.paths.media_uploads'), '/') . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $publicRoot = ROOT_PATH . '/public/';
        $webPath = str_replace($publicRoot, '', $uploadPath);
        if ($webPath === $uploadPath) {
            $webPath = 'uploads/media/';
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        foreach (array_unique($imageUrls) as $imageUrl) {
            $imageUrl = trim($imageUrl);
            if ($imageUrl === '' || !filter_var($imageUrl, FILTER_VALIDATE_URL)) continue;

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $imageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_MAXFILESIZE, 15 * 1024 * 1024); // 15MB safety cap
                $bytes = curl_exec($ch);
                curl_close($ch);

                if ($bytes === false || $bytes === '') continue;

                // Never trust the URL's extension — sniff the real content,
                // same as a direct user upload through the media library.
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_buffer($finfo, $bytes);
                finfo_close($finfo);

                if (!isset($allowedMimes[$detectedMime])) continue;
                $ext = $allowedMimes[$detectedMime];

                $hashName = bin2hex(random_bytes(16)) . '.' . $ext;
                if (file_put_contents($uploadPath . $hashName, $bytes) === false) continue;

                $originalName = basename(parse_url($imageUrl, PHP_URL_PATH) ?? $hashName);

                $mediaFileId = $db->execute(
                    "INSERT INTO media_files (filename, original_name, filepath, file_type, file_size, title, alt_text)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $hashName,
                        $originalName,
                        $webPath . $hashName,
                        $detectedMime,
                        strlen($bytes),
                        pathinfo($originalName, PATHINFO_FILENAME),
                        pathinfo($originalName, PATHINFO_FILENAME),
                    ]
                ) ? $db->lastInsertId() : null;

                if ($mediaFileId) {
                    $db->execute(
                        "INSERT IGNORE INTO media_links (media_file_id, entity_type, entity_id) VALUES (?, 'catalog_toys', ?)",
                        [$mediaFileId, $catalogToyId]
                    );
                }
            } catch (\Exception $e) {
                // One bad image shouldn't fail the whole import — skip it.
                continue;
            }
        }
    }
}
