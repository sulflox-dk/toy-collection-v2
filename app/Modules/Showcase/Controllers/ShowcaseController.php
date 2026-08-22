<?php
namespace App\Modules\Showcase\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Database\Database;
use App\Kernel\Core\Config;
use App\Kernel\Auth\Auth;

/**
 * Public, read-only "front of house" view of the collection — no login required to browse.
 * The one write-capable affordance (the Edit-in-Admin button) only renders, and only works,
 * for a visitor who happens to already be signed in as an admin in this same browser session.
 */
class ShowcaseController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::getInstance();
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';
        $isAdmin = Auth::check() && Auth::isAdmin();

        $toys = $db->query("
            SELECT
                ct.id, ct.catalog_toy_id, ct.cherish_rating, ct.notes, ct.date_acquired,
                src.name AS source_name,
                su.box_code AS storage_box_code, su.name AS storage_name,
                ast.name AS status_name,
                cg.name AS condition_grade_name,
                cat.name AS toy_name, cat.slug AS toy_slug, cat.year_released, cat.wave,
                cat.assortment_sku, cat.description,
                u.name AS universe_name, u.slug AS universe_slug, u.description AS universe_description,
                m.name AS manufacturer_name,
                tl.name AS toy_line_name,
                pt.name AS product_type_name
            FROM collection_toys ct
            JOIN catalog_toys cat ON ct.catalog_toy_id = cat.id
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_manufacturers m ON cat.manufacturer_id = m.id
            LEFT JOIN meta_toy_lines tl ON cat.toy_line_id = tl.id
            LEFT JOIN meta_product_types pt ON cat.product_type_id = pt.id
            LEFT JOIN collection_sources src ON ct.purchase_source_id = src.id
            LEFT JOIN collection_storage_units su ON ct.storage_unit_id = su.id
            LEFT JOIN meta_acquisition_statuses ast ON ct.acquisition_status_id = ast.id
            LEFT JOIN meta_condition_grades cg ON ct.condition_grade_id = cg.id
            WHERE ct.deleted_at IS NULL
            ORDER BY u.name ASC, cat.name ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // Parts & accessories per collection toy — same presence rule as the Missing Parts
        // report: a catalog item with no matching collection_toy_items row counts as missing,
        // not "assumed present". Keeps the two pages honest with each other.
        $itemRows = $db->query("
            SELECT
                ct.id AS collection_toy_id,
                s.name AS part_name, s.type AS part_type,
                owned.is_present, owned.is_repro
            FROM collection_toys ct
            JOIN catalog_toy_items cti ON cti.catalog_toy_id = ct.catalog_toy_id
            JOIN meta_subjects s ON cti.subject_id = s.id
            LEFT JOIN collection_toy_items owned
                ON owned.collection_toy_id = ct.id AND owned.catalog_toy_item_id = cti.id
            WHERE ct.deleted_at IS NULL
            ORDER BY ct.id ASC, s.name ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $itemsByToy = [];
        foreach ($itemRows as $row) {
            $itemsByToy[$row['collection_toy_id']][] = [
                'name' => $row['part_name'],
                'type' => $row['part_type'] ?: 'Accessory',
                'present' => $row['is_present'] !== null ? (bool) $row['is_present'] : false,
                'repro' => (bool) $row['is_repro'],
            ];
        }

        // Real photos, when there are any — the collection entry's own first, falling back to
        // the catalog toy's stock shot. Toys with neither fall back to the generative artwork
        // client-side.
        $collectionMedia = $this->mediaByEntity($db, 'collection_toys');
        $catalogMedia = $this->mediaByEntity($db, 'catalog_toys');

        $universes = [];
        $collection = [];

        foreach ($toys as $t) {
            $slug = $t['universe_slug'] ?? 'other';
            if (!isset($universes[$slug])) {
                $universes[$slug] = [
                    'slug' => $slug,
                    'name' => $t['universe_name'] ?? 'Uncategorized',
                    'description' => $t['universe_description'] ?: null,
                    'years' => [],
                    'count' => 0,
                ];
            }
            $universes[$slug]['count']++;
            if ($t['year_released']) {
                $universes[$slug]['years'][] = (int) $t['year_released'];
            }

            $photos = $collectionMedia[$t['id']] ?? ($catalogMedia[$t['catalog_toy_id']] ?? []);
            $photoUrls = array_values(array_map(
                static fn($p) => $baseUrl . ltrim($p['filepath'], '/'),
                $photos
            ));

            $collection[] = [
                'id' => (int) $t['id'],
                'catalogId' => (int) $t['catalog_toy_id'],
                'name' => $t['toy_name'],
                'universe' => $t['universe_name'] ?? 'Uncategorized',
                'universeSlug' => $slug,
                'manufacturer' => $t['manufacturer_name'] ?: '—',
                'toyLine' => $t['toy_line_name'] ?: '—',
                'productType' => $t['product_type_name'] ?: 'Action Figure',
                'year' => $t['year_released'] ? (int) $t['year_released'] : null,
                'wave' => $t['wave'] ?: '',
                'sku' => $t['assortment_sku'] ?: '',
                'description' => $t['description'] ?: '',
                'status' => $t['status_name'] ?: 'Arrived',
                'storageUnit' => $t['storage_box_code']
                    ? $t['storage_box_code'] . ' - ' . $t['storage_name']
                    : ($t['storage_name'] ?: null),
                'source' => $t['source_name'] ?: null,
                'dateAcquired' => $t['date_acquired'],
                'conditionGrade' => $t['condition_grade_name'] ?: null,
                'cherish' => $t['cherish_rating'] !== null ? (int) $t['cherish_rating'] : null,
                'notes' => $t['notes'] ?: null,
                'items' => $itemsByToy[$t['id']] ?? [],
                'photos' => $photoUrls,
            ];
        }

        $universeList = [];
        foreach ($universes as $u) {
            $blurb = $u['description'];
            if (!$blurb) {
                $blurb = $u['years']
                    ? $u['count'] . ' toy' . ($u['count'] === 1 ? '' : 's') . ' · ' . min($u['years']) . '\u{2013}' . max($u['years'])
                    : $u['count'] . ' toy' . ($u['count'] === 1 ? '' : 's');
            }
            $universeList[] = [
                'slug' => $u['slug'],
                'name' => $u['name'],
                'blurb' => $blurb,
            ];
        }
        usort($universeList, static fn($a, $b) => strcmp($a['name'], $b['name']));

        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;

        $this->withoutLayout()->render('showcase_index', [
            'title' => 'The Display Case',
            'collectionJson' => json_encode($collection, $jsonFlags),
            'universesJson' => json_encode($universeList, $jsonFlags),
            'isAdmin' => $isAdmin,
            'baseUrl' => $baseUrl,
        ]);
    }

    /** @return array<int, array{filepath: string}[]> media rows grouped by entity_id */
    private function mediaByEntity(Database $db, string $entityType): array
    {
        $rows = $db->query("
            SELECT ml.entity_id, f.filepath
            FROM media_links ml
            JOIN media_files f ON ml.media_file_id = f.id
            WHERE ml.entity_type = ?
            ORDER BY ml.entity_id ASC, ml.is_featured DESC, ml.sort_order ASC
        ", [$entityType])->fetchAll(\PDO::FETCH_ASSOC);

        $byEntity = [];
        foreach ($rows as $row) {
            $byEntity[$row['entity_id']][] = ['filepath' => $row['filepath']];
        }
        return $byEntity;
    }
}
