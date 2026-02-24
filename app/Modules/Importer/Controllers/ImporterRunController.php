<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Modules\Importer\Models\ImporterSource;
use App\Modules\Importer\Models\ImporterItem;
use App\Modules\Importer\Models\ImporterLog;
use App\Modules\Importer\Drivers\SiteDriverInterface;

class ImporterRunController extends Controller
{
    public function index(Request $request): void
    {
        $stats = ImporterSource::getStats();

        $this->render('importer_run_index', [
            'title'   => 'Run Import',
            'stats'   => $stats,
            'scripts' => [
                'assets/js/modules/importer/importer_run.js'
            ]
        ]);
    }

    /**
     * AJAX: Analyze a URL and return preview data.
     * POST /importer-run/preview
     */
    public function preview(Request $request): void
    {
        $url = trim($request->input('url', ''));

        if ($url === '') {
            $this->json(['error' => 'Please enter a URL'], 400);
            return;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json(['error' => 'Invalid URL format'], 400);
            return;
        }

        // Find matching source
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

            if ($driver->isOverviewPage($url)) {
                $detailUrls = $driver->parseOverviewPage($url);
                // Limit to first 20 items on overview pages
                $detailUrls = array_slice($detailUrls, 0, 20);

                foreach ($detailUrls as $detailUrl) {
                    try {
                        $toysToProcess[] = $driver->parseSinglePage($detailUrl);
                    } catch (\Exception $e) {
                        // Skip individual failures, continue with rest
                        continue;
                    }
                }
            } else {
                $toysToProcess[] = $driver->parseSinglePage($url);
            }

            // Conflict check against existing data
            $db = Database::getInstance();
            $results = [];

            foreach ($toysToProcess as $dto) {
                $item = $dto->toArray();

                // Check if already imported via external ID
                $linkedItem = ImporterItem::findByExternal((int) $source['id'], $dto->externalId);

                if ($linkedItem) {
                    $item['status'] = 'linked';
                    $item['existingId'] = $linkedItem['catalog_toy_id'];
                    $item['matchReason'] = 'External ID Match';
                } else {
                    // Check by name in catalog
                    $existingToy = $db->fetch(
                        "SELECT id, name FROM catalog_toys WHERE name = ? LIMIT 1",
                        [$dto->name]
                    );

                    if ($existingToy) {
                        $item['status'] = 'conflict';
                        $item['existingId'] = $existingToy['id'];
                        $item['matchReason'] = 'Name Match';
                    } else {
                        $item['status'] = 'new';
                    }
                }

                $item['source_id'] = (int) $source['id'];
                $results[] = $item;
            }

            $this->json([
                'success' => true,
                'data' => $results,
                'source' => $source['name'],
                'count' => count($results)
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Scraping failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Execute the import for selected items.
     * POST /importer-run/import
     */
    public function runImport(Request $request): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            $this->json(['error' => 'No items selected'], 400);
            return;
        }

        $db = Database::getInstance();
        $successCount = 0;
        $errors = [];

        foreach ($items as $item) {
            $importItemId = null;
            $inTransaction = false;

            try {
                $sourceId = (int) ($item['source_id'] ?? 0);
                $externalId = $item['externalId'] ?? '';
                $externalUrl = $item['externalUrl'] ?? '';

                if (!$sourceId || !$externalId) {
                    throw new \RuntimeException('Missing source_id or externalId');
                }

                // Step 1: Register import intent
                $initialCatalogToyId = !empty($item['existingId']) ? (int) $item['existingId'] : null;

                $importItemId = ImporterItem::registerImport(
                    $sourceId,
                    $initialCatalogToyId,
                    $externalId,
                    $externalUrl
                );

                $catalogToyId = null;
                $action = '';

                if (!empty($item['existingId'])) {
                    // Update existing - just link it
                    $catalogToyId = (int) $item['existingId'];
                    $action = 'Success';

                } else {
                    // Create new catalog toy
                    $db->beginTransaction();
                    $inTransaction = true;

                    $name = trim($item['name'] ?? 'Unknown');
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . '-' . time() . '-' . mt_rand(100, 999);
                    $yearReleased = !empty($item['year']) ? (int) $item['year'] : null;
                    $wave = $item['wave'] ?? '';
                    $assortmentSku = $item['assortmentSku'] ?? '';

                    // Look up manufacturer by name
                    $manufacturerId = null;
                    if (!empty($item['manufacturer'])) {
                        $mfg = $db->fetch(
                            "SELECT id FROM meta_manufacturers WHERE name = ? LIMIT 1",
                            [$item['manufacturer']]
                        );
                        if ($mfg) {
                            $manufacturerId = (int) $mfg['id'];
                        }
                    }

                    // Look up toy line by name
                    $toyLineId = null;
                    if (!empty($item['toyLine'])) {
                        $tl = $db->fetch(
                            "SELECT id FROM meta_toy_lines WHERE name = ? LIMIT 1",
                            [$item['toyLine']]
                        );
                        if ($tl) {
                            $toyLineId = (int) $tl['id'];
                        }
                    }

                    $db->execute(
                        "INSERT INTO catalog_toys
                            (name, slug, year_released, wave, assortment_sku, manufacturer_id, toy_line_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$name, $slug, $yearReleased, $wave, $assortmentSku, $manufacturerId, $toyLineId]
                    );
                    $catalogToyId = $db->lastInsertId();

                    // Create catalog toy items (accessories)
                    if (!empty($item['items']) && is_array($item['items'])) {
                        foreach ($item['items'] as $accessoryName) {
                            $accessoryName = trim($accessoryName);
                            if ($accessoryName === '') continue;

                            $db->execute(
                                "INSERT INTO catalog_toy_items (catalog_toy_id, description) VALUES (?, ?)",
                                [$catalogToyId, $accessoryName]
                            );
                        }
                    }

                    // Update import item with the new catalog toy ID
                    ImporterItem::registerImport($sourceId, $catalogToyId, $externalId, $externalUrl);

                    $db->commit();
                    $inTransaction = false;
                    $action = 'Success';
                }

                ImporterLog::log($sourceId, $action, $importItemId, "Imported: {$item['name']}");
                $successCount++;

            } catch (\Exception $e) {
                if ($inTransaction) {
                    $db->rollBack();
                    $inTransaction = false;
                }

                $errorMsg = "Failed: " . ($item['name'] ?? 'unknown') . " - " . $e->getMessage();
                $errors[] = $errorMsg;

                if ($importItemId) {
                    ImporterLog::log(
                        (int) ($item['source_id'] ?? 0),
                        'Error',
                        $importItemId,
                        $errorMsg
                    );
                }
            }
        }

        $this->json([
            'success' => true,
            'count' => $successCount,
            'errors' => $errors
        ]);
    }
}
