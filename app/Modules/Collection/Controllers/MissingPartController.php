<?php
namespace App\Modules\Collection\Controllers;

use App\Kernel\Core\Config;
use App\Kernel\Database\Database;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Meta\Models\Universe;
use App\Modules\Meta\Models\Manufacturer;
use App\Modules\Meta\Models\ToyLine;
use App\Modules\Meta\Models\ProductType;

class MissingPartController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('missing_part_index', [
            'title' => 'Missing Parts',
            'universes' => Universe::all(),
            'manufacturers' => Manufacturer::all(),
            'toyLines' => ToyLine::all(),
            'productTypes' => ProductType::all(),
            'scripts' => [
                'assets/js/modules/collection/collection_toys.js',
                'assets/js/modules/collection/missing_parts.js',
            ]
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;

        $partSearch = trim($request->input('part_q', ''));
        $toySearch = trim($request->input('toy_q', ''));
        $universeId = (int) $request->input('universe_id', 0);
        $toyLineId = (int) $request->input('toy_line_id', 0);
        $manufacturerId = (int) $request->input('manufacturer_id', 0);
        $productTypeId = (int) $request->input('product_type_id', 0);
        $showRepro = filter_var($request->input('show_repro', false), FILTER_VALIDATE_BOOLEAN);
        $sort = $request->input('sort', 'part');

        $data = $this->getMissingParts(
            $page,
            $perPage,
            $partSearch,
            $toySearch,
            $universeId,
            $toyLineId,
            $manufacturerId,
            $productTypeId,
            $showRepro,
            $sort
        );

        $this->renderPartial('missing_part_list', [
            'parts' => $data['items'],
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    private function getMissingParts(
        int $page,
        int $perPage,
        string $partSearch,
        string $toySearch,
        int $universeId,
        int $toyLineId,
        int $manufacturerId,
        int $productTypeId,
        bool $showRepro,
        string $sort
    ): array {
        $db = Database::getInstance();
        $baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';

        $offset = ($page - 1) * $perPage;
        // Two placeholders up front for the image_path fallback subqueries,
        // which appear in the SELECT before any WHERE clause placeholder.
        $params = [$baseUrl, $baseUrl];
        $whereConditions = ['ct.deleted_at IS NULL'];

        // A row is a "problem part" if either:
        //  - nobody has ever marked it present for this specific owned toy, or
        //  - it's explicitly marked not present, or
        //  - (when requested) it IS present but flagged as a reproduction.
        // This mirrors the exact logic already used for the "missing" tooltip
        // on the Collection cards (see CollectionToy::getPaginatedWithDetails),
        // so the two stay consistent instead of disagreeing with each other.
        if ($showRepro) {
            $statusCondition = '(owned.id IS NULL OR owned.is_present = 0 OR owned.is_repro = 1)';
        } else {
            $statusCondition = '(owned.id IS NULL OR owned.is_present = 0)';
        }
        $whereConditions[] = $statusCondition;

        if ($partSearch !== '') {
            $whereConditions[] = '(s.name LIKE ? OR cti.description LIKE ?)';
            $params[] = "%$partSearch%";
            $params[] = "%$partSearch%";
        }

        if ($toySearch !== '') {
            $whereConditions[] = 'cat.name LIKE ?';
            $params[] = "%$toySearch%";
        }

        if ($universeId > 0) {
            $whereConditions[] = 'cat.universe_id = ?';
            $params[] = $universeId;
        }

        if ($toyLineId > 0) {
            $whereConditions[] = 'cat.toy_line_id = ?';
            $params[] = $toyLineId;
        }

        if ($manufacturerId > 0) {
            $whereConditions[] = 'cat.manufacturer_id = ?';
            $params[] = $manufacturerId;
        }

        if ($productTypeId > 0) {
            $whereConditions[] = 'cat.product_type_id = ?';
            $params[] = $productTypeId;
        }

        $orderBy = match ($sort) {
            'toy' => 'cat.name ASC, s.name ASC',
            'cherish' => '(ct.cherish_rating IS NULL) ASC, ct.cherish_rating DESC, cat.name ASC',
            default => 's.name ASC, cat.name ASC',
        };

        $sql = "
            SELECT
                cti.id AS catalog_toy_item_id,
                s.name AS part_name,
                s.type AS part_type,
                CASE WHEN owned.id IS NOT NULL AND owned.is_present = 1 AND owned.is_repro = 1
                     THEN 'repro' ELSE 'missing' END AS problem_status,
                ct.id AS collection_toy_id,
                ct.cherish_rating,
                cat.id AS catalog_toy_id,
                cat.name AS toy_name,
                cat.year_released,
                cat.wave,
                u.name AS universe_name,
                m.name AS manufacturer_name,
                tl.name AS toy_line_name,
                pt.name AS product_type_name,
                COALESCE(
                    (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                     WHERE ml.entity_type = 'collection_toys' AND ml.entity_id = ct.id
                     ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1),
                    (SELECT CONCAT(?, f.filepath) FROM media_links ml JOIN media_files f ON ml.media_file_id = f.id
                     WHERE ml.entity_type = 'catalog_toys' AND ml.entity_id = cat.id
                     ORDER BY ml.is_featured DESC, ml.sort_order ASC LIMIT 1)
                ) AS image_path
            FROM collection_toys ct
            JOIN catalog_toys cat ON ct.catalog_toy_id = cat.id
            JOIN catalog_toy_items cti ON cti.catalog_toy_id = cat.id
            JOIN meta_subjects s ON cti.subject_id = s.id
            LEFT JOIN meta_universes u ON cat.universe_id = u.id
            LEFT JOIN meta_manufacturers m ON cat.manufacturer_id = m.id
            LEFT JOIN meta_toy_lines tl ON cat.toy_line_id = tl.id
            LEFT JOIN meta_product_types pt ON cat.product_type_id = pt.id
            LEFT JOIN collection_toy_items owned
                ON owned.collection_toy_id = ct.id AND owned.catalog_toy_item_id = cti.id
            WHERE " . implode(' AND ', $whereConditions);

        $countSql = "SELECT COUNT(*) FROM ($sql) AS sub";
        $total = (int) $db->query($countSql, $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        $sql .= " ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = $db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }
}
