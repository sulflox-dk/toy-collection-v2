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
                (SELECT f.filepath FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                 WHERE ml.entity_type = 'universes' AND ml.entity_id = u.id
                 ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1) AS universe_image_path,
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

        $sourcesByToy = $this->sourceCreditsByToy($db, $baseUrl);

        $universes = [];
        $collection = [];

        foreach ($toys as $t) {
            $slug = $t['universe_slug'] ?? 'other';
            if (!isset($universes[$slug])) {
                $universes[$slug] = [
                    'slug' => $slug,
                    'name' => $t['universe_name'] ?? 'Uncategorized',
                    'description' => $t['universe_description'] ?: null,
                    'image' => $t['universe_image_path'] ? $baseUrl . ltrim($t['universe_image_path'], '/') : null,
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
                // Deliberately separate from 'source' above (that's where the
                // collector bought THIS physical copy) — these are the sites
                // the catalog data itself was imported from.
                'sourceCredits' => $sourcesByToy[$t['catalog_toy_id']] ?? [],
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
                'image' => $u['image'],
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

    /**
     * Per catalog toy, one entry per distinct import source that
     * contributed to it — its description (already-sanitized HTML, safe
     * to output as-is) and every photo (the toy's own plus its
     * accessories') that came from that same source. Grouped by
     * source_url, since that's the key catalog_toy_descriptions itself
     * upserts on — a source with only photos and no saved description
     * (or vice versa) still gets an entry, just with the other half empty.
     *
     * @return array<int, array<int, array{name: ?string, url: string, description: ?string, images: string[]}>>
     */
    private function sourceCreditsByToy(Database $db, string $baseUrl): array
    {
        $bySourceUrl = []; // catalog_toy_id => [source_url => credit]

        $descriptionRows = $db->query("
            SELECT catalog_toy_id, description, source_name, source_url
            FROM catalog_toy_descriptions
            WHERE source_url IS NOT NULL
            ORDER BY id ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($descriptionRows as $row) {
            $toyId = (int) $row['catalog_toy_id'];
            $url = $row['source_url'];
            $bySourceUrl[$toyId][$url] ??= ['name' => null, 'url' => $url, 'description' => null, 'images' => []];
            $bySourceUrl[$toyId][$url]['name'] = $row['source_name'] ?: $bySourceUrl[$toyId][$url]['name'];
            $bySourceUrl[$toyId][$url]['description'] = $row['description'];
        }

        // Photos on the toy itself, plus every one of its accessories' —
        // both count as "photos that came from that site" for this toy.
        $imageRows = $db->query("
            SELECT cat.id AS catalog_toy_id, f.filepath, f.source_name, f.source_url
            FROM media_files f
            JOIN media_links ml ON ml.media_file_id = f.id
            JOIN catalog_toys cat ON (
                (ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id)
                OR (ml.entity_type = 'catalog_toy_items' AND ml.entity_id IN (
                    SELECT id FROM catalog_toy_items WHERE catalog_toy_id = cat.id
                ))
            )
            WHERE f.source_url IS NOT NULL
            ORDER BY cat.id ASC, ml.is_featured DESC, ml.sort_order ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($imageRows as $row) {
            $toyId = (int) $row['catalog_toy_id'];
            $url = $row['source_url'];
            $bySourceUrl[$toyId][$url] ??= ['name' => null, 'url' => $url, 'description' => null, 'images' => []];
            $bySourceUrl[$toyId][$url]['name'] = $bySourceUrl[$toyId][$url]['name'] ?: $row['source_name'];
            $bySourceUrl[$toyId][$url]['images'][] = $baseUrl . ltrim($row['filepath'], '/');
        }

        $byToy = [];
        foreach ($bySourceUrl as $toyId => $credits) {
            $byToy[$toyId] = array_values($credits);
        }
        return $byToy;
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
