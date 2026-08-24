<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class TransformerlandDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Transformerland';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'transformerland.com') !== false;
    }

    public function isOverviewPage(string $url): bool
    {
        return false;
    }

    public function parseOverviewPage(string $url): array
    {
        return [];
    }

    public function parseSinglePage(string $url): ScrapedToyDTO
    {
        $html = $this->fetchUrl($url);

        if (empty($html)) {
            throw new \RuntimeException("Empty HTML returned from URL");
        }

        $xpath = $this->createXPath($html);

        // Every image src on this site is root-relative (e.g.
        // "/image/reference_images/x.jpg"), so the bare domain is enough.
        $base = 'https://www.transformerland.com';

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        // The page's own "parentid" hidden field is the toy's real ID —
        // the numeric IDs in image filenames are per-image asset IDs, not
        // the toy's, and would break dedup against a re-import.
        $parentId = $this->getText($xpath, "//input[@name='parentid']/@value");
        if ($parentId !== '') {
            $dto->externalId = $parentId;
        } else {
            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
            $dto->externalId = $pathInfo['filename'] ?? md5($url);
        }

        $dto->name = $this->getText($xpath, "//p[contains(@class,'details_name')]") ?: 'Unknown Toy';

        // The "Toy Line:" fact is actually the whole franchise (e.g. "Star
        // Wars") — that's this app's Universe, which no driver scrapes
        // (left for the batch default / manual pick). "Series:" (e.g.
        // "Original Kenner Series") is this site's equivalent of our own
        // Toy Line concept, so that's what gets mapped.
        $franchise = $this->factValue($xpath, 'Toy Line');
        $dto->toyLine = $this->factValue($xpath, 'Series');
        $dto->year = $this->factValue($xpath, 'Year');

        // No structured manufacturer field exists anywhere on the page —
        // the only place it appears is smuggled into the price-graph
        // form's action, as "<Manufacturer> <Franchise>" with no
        // separator (e.g. "/price/Kenner Star Wars/Basic Class/4-LOM").
        // Stripping the already-known franchise text off the end of that
        // first segment recovers just the manufacturer, rather than
        // guessing at word boundaries.
        $priceAction = $this->getText($xpath, "//form[@name='theform']/@action");
        $dto->manufacturer = $this->extractManufacturer($priceAction, $franchise);

        // No narrative description exists on this page (the content block
        // is empty) — deliberately left unset rather than guessed.

        // Main product photo. Real photos on this site are the full-size
        // <a href> of a MagicZoomPlus link, not the <img src> next to it
        // (which is just a thumbnail) — and a "0.jpg" href is this site's
        // sentinel for "no image available yet", not a real photo.
        $heroUrl = $this->magicZoomHref($xpath, "//div[contains(@class,'info_img_block')]//a[contains(@class,'MagicZoomPlus')]");
        if ($heroUrl) {
            $dto->images[] = $this->fixRelativeUrl($heroUrl, $base);
        }

        // "Set Figures" — extra photos of the toy itself (the same figure,
        // sometimes from another angle or a set/box shot).
        foreach ($this->archiveGroupItems($xpath, 'Set Figures') as $item) {
            if ($item['imageUrl']) {
                $dto->images[] = $this->fixRelativeUrl($item['imageUrl'], $base);
            }
        }

        // "Set Accessories" — exactly what makes this source worth having:
        // each accessory listed with its own dedicated photo.
        foreach ($this->archiveGroupItems($xpath, 'Set Accessories') as $item) {
            $dto->items[] = $item['name'];
            if ($item['imageUrl']) {
                $dto->itemImages[$item['name']] = $this->fixRelativeUrl($item['imageUrl'], $base);
            }
        }

        return $dto;
    }

    /**
     * Read a "Label: value" row from the summary facts table
     * (<div class="toy_facts">...<tr><th>Label:</th><td>value</td></tr>).
     */
    private function factValue(\DOMXPath $xpath, string $label): string
    {
        $nodes = $xpath->query(
            "//div[contains(@class,'toy_facts')]//tr[th[contains(normalize-space(.), '{$label}')]]/td"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }
        return trim(preg_replace('/\s+/', ' ', $nodes->item(0)->textContent));
    }

    /**
     * The href of the first MagicZoomPlus link matching $query, or null if
     * there isn't one or it's this site's "0.jpg" no-image placeholder.
     */
    private function magicZoomHref(\DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);
        if (!$nodes || $nodes->length === 0 || !$nodes->item(0) instanceof \DOMElement) {
            return null;
        }
        $href = $nodes->item(0)->getAttribute('href');
        if ($href === '' || preg_match('#/0\.jpg$#i', $href)) {
            return null;
        }
        return $href;
    }

    /**
     * Every ".ArchiveToyImg" entry inside the "<p class="ArchiveGroupName">
     * $groupName</p>" group — each is one figure/accessory with a name
     * (its ".ToyDesc-Title", quantity suffix like " (x1)" stripped) and,
     * where a real photo exists, its image URL.
     *
     * @return array<int, array{name: string, imageUrl: ?string}>
     */
    private function archiveGroupItems(\DOMXPath $xpath, string $groupName): array
    {
        $groupNodes = $xpath->query(
            "//div[contains(@class,'ArchiveGroupWrapper')][.//p[contains(@class,'ArchiveGroupName') and normalize-space(.)='{$groupName}']]"
        );
        if (!$groupNodes || $groupNodes->length === 0) {
            return [];
        }
        $group = $groupNodes->item(0);

        $items = [];
        foreach ($xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' ArchiveToyImg ')]", $group) as $itemNode) {
            $titleNodes = $xpath->query(".//div[contains(@class,'ToyDesc-Title')]", $itemNode);
            if (!$titleNodes || $titleNodes->length === 0) {
                continue;
            }
            $title = trim(preg_replace('/\s+/', ' ', $titleNodes->item(0)->textContent));
            $title = trim(preg_replace('/\s*\(x\d+\)\s*$/i', '', $title));
            if ($title === '') {
                continue;
            }

            $imageUrl = null;
            $linkNodes = $xpath->query(".//a[contains(@class,'MagicZoomPlus')]", $itemNode);
            if ($linkNodes && $linkNodes->length > 0 && $linkNodes->item(0) instanceof \DOMElement) {
                $href = $linkNodes->item(0)->getAttribute('href');
                if ($href !== '' && !preg_match('#/0\.jpg$#i', $href)) {
                    $imageUrl = $href;
                }
            }

            $items[] = ['name' => $title, 'imageUrl' => $imageUrl];
        }

        return $items;
    }

    /**
     * "$priceAction" looks like "/price/Kenner Star Wars/Basic Class/4-LOM"
     * — the manufacturer and franchise smashed together with no separator
     * in the first segment. Stripping the already-known franchise text off
     * the end recovers just the manufacturer; if it doesn't cleanly match
     * (a different site layout, or franchise not found), leave it unset
     * rather than guess at a word boundary.
     */
    private function extractManufacturer(string $priceAction, string $franchise): string
    {
        $segments = explode('/', trim($priceAction, '/'));
        if (count($segments) < 2 || $franchise === '') {
            return '';
        }

        $manufacturerFranchise = trim($segments[1]);
        if (!str_ends_with($manufacturerFranchise, $franchise)) {
            return '';
        }

        return trim(substr($manufacturerFranchise, 0, -strlen($franchise)));
    }
}
