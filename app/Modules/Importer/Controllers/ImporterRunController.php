<?php
namespace App\Modules\Importer\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
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

        $this->render('importer_run_index', [
            'title'   => 'Run Import',
            'stats'   => $stats,
            'universes' => Universe::all(),
            'manufacturers' => Manufacturer::all(),
            'toyLines' => $toyLines,
            'productTypes' => ProductType::all(),
            'entertainmentSources' => EntertainmentSource::all(),
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
        $offset = max(0, (int) $request->input('offset', 0));

        // Batch defaults: when set, these are an explicit instruction from
        // the user ("this whole batch is Hasbro / Vintage Collection"), and
        // take priority over whatever the scraper guessed — auto-detected
        // manufacturer/toy line only ever match on an exact string, so an
        // explicit choice here is strictly more reliable.
        $batchUniverseId = (int) $request->input('universe_id', 0) ?: null;
        $batchManufacturerId = (int) $request->input('manufacturer_id', 0) ?: null;
        $batchToyLineId = (int) $request->input('toy_line_id', 0) ?: null;

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
            $totalFound = null;

            if ($driver->isOverviewPage($url)) {
                $detailUrls = $driver->parseOverviewPage($url);
                $totalFound = count($detailUrls);
                // 20 at a time, starting from the given offset — re-run with
                // a higher offset to page through a long listing.
                $pageUrls = array_slice($detailUrls, $offset, 20);

                foreach ($pageUrls as $detailUrl) {
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

                // Resolve universe/manufacturer/toy line to real IDs so the
                // preview grid can pre-select them: the batch default wins
                // when set, otherwise fall back to an exact-name match
                // against what the scraper found.
                $item['universe_id'] = $batchUniverseId;

                if ($batchManufacturerId) {
                    $item['manufacturer_id'] = $batchManufacturerId;
                } else {
                    $item['manufacturer_id'] = null;
                    if (!empty($item['manufacturer'])) {
                        $mfg = $db->fetch("SELECT id FROM meta_manufacturers WHERE name = ? LIMIT 1", [$item['manufacturer']]);
                        if ($mfg) $item['manufacturer_id'] = (int) $mfg['id'];
                    }
                }

                if ($batchToyLineId) {
                    $item['toy_line_id'] = $batchToyLineId;
                } else {
                    $item['toy_line_id'] = null;
                    if (!empty($item['toyLine'])) {
                        $tl = $db->fetch("SELECT id FROM meta_toy_lines WHERE name = ? LIMIT 1", [$item['toyLine']]);
                        if ($tl) $item['toy_line_id'] = (int) $tl['id'];
                    }
                }

                // The scraper has no way to know these — always left for the
                // preview grid to fill in per toy.
                $item['product_type_id'] = null;
                $item['entertainment_source_id'] = null;

                $item['source_id'] = (int) $source['id'];
                $results[] = $item;
            }

            $this->json([
                'success' => true,
                'data' => $results,
                'source' => $source['name'],
                'count' => count($results),
                'offset' => $offset,
                'totalFound' => $totalFound, // null for single-page imports
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

                    // These now come as real IDs straight from the preview
                    // grid's dropdowns (batch default or per-toy override,
                    // whichever the user left selected) rather than being
                    // re-derived from a fuzzy name match here.
                    $universeId = (int) ($item['universe_id'] ?? 0) ?: null;
                    $manufacturerId = (int) ($item['manufacturer_id'] ?? 0) ?: null;
                    $toyLineId = (int) ($item['toy_line_id'] ?? 0) ?: null;
                    $productTypeId = (int) ($item['product_type_id'] ?? 0) ?: null;
                    $entertainmentSourceId = (int) ($item['entertainment_source_id'] ?? 0) ?: null;

                    $db->execute(
                        "INSERT INTO catalog_toys
                            (name, slug, year_released, wave, assortment_sku, manufacturer_id, toy_line_id, universe_id, product_type_id, entertainment_source_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$name, $slug, $yearReleased, $wave, $assortmentSku, $manufacturerId, $toyLineId, $universeId, $productTypeId, $entertainmentSourceId]
                    );
                    $catalogToyId = $db->lastInsertId();

                    // Create catalog toy items (accessories).
                    // catalog_toy_items.subject_id is required (see migration
                    // 020) — a scraped accessory only gives us a name, so we
                    // find-or-create a matching meta_subjects row for it.
                    if (!empty($item['items']) && is_array($item['items'])) {
                        foreach ($item['items'] as $accessoryName) {
                            $accessoryName = trim($accessoryName);
                            if ($accessoryName === '') continue;

                            $subject = $db->fetch(
                                "SELECT id FROM meta_subjects WHERE name = ? LIMIT 1",
                                [$accessoryName]
                            );

                            if ($subject) {
                                $subjectId = (int) $subject['id'];
                            } else {
                                $subjectSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $accessoryName), '-'))
                                    . '-' . time() . '-' . mt_rand(100, 999);

                                $db->execute(
                                    "INSERT INTO meta_subjects (name, slug, type) VALUES (?, ?, 'Accessory')",
                                    [$accessoryName, $subjectSlug]
                                );
                                $subjectId = $db->lastInsertId();
                            }

                            $db->execute(
                                "INSERT INTO catalog_toy_items (catalog_toy_id, subject_id, description) VALUES (?, ?, NULL)",
                                [$catalogToyId, $subjectId]
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
