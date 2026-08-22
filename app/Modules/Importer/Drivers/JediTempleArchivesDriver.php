<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class JediTempleArchivesDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Jedi Temple Archives';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'jeditemplearchives.com') !== false;
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

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
        $dto->externalId = $pathInfo['filename'] ?? md5($url);

        // The "InfoBox" fields (Name/Collection/License/Availability/
        // Accessory Details) are a clean, structured summary of the exact
        // figure this page is about — far more reliable than screen-scraping
        // the <title>, which mashes name, manufacturer, and figure number
        // together with no consistent separator to split back apart.
        $dto->name = $this->infoValue($xpath, 'Name') ?: 'Unknown Toy';
        $dto->manufacturer = $this->infoValue($xpath, 'License');

        // "Collection" reads like "The Vintage Collection (Basic Figures –
        // VC04)" — the parenthetical's last dash-separated segment is the
        // figure/collection number (assortmentSku); the rest, with the
        // parenthetical dropped, is the toy line.
        $collection = $this->infoValue($xpath, 'Collection');
        if (preg_match('/\(([^)]*)\)\s*$/u', $collection, $m)) {
            $segments = preg_split('/[-\x{2013}\x{2014}]/u', $m[1]);
            $dto->assortmentSku = trim((string) end($segments));
        }
        $dto->toyLine = trim(preg_replace('/\s*\([^)]*\)\s*$/u', '', $collection));

        // "Availability" (e.g. "August 2010") is this page's one clean,
        // unambiguous year — the alternative, the Packaging Details table,
        // often lists several years across a figure's re-release variants.
        $availability = $this->infoValue($xpath, 'Availability');
        if (preg_match('/(\d{4})/', $availability, $m)) {
            $dto->year = $m[1];
        }

        // The sidebar's "Related Guides: Wave N" heading is this site's
        // only distinct wave value — Collection's own number (VC04, above)
        // is the figure/assortment number, not the wave.
        $relatedHeading = $this->getText($xpath, "//h2[starts-with(normalize-space(.), 'Related Guides')]");
        if (preg_match('/Wave\s*(\d+)/i', $relatedHeading, $m)) {
            $dto->wave = $m[1];
        }

        // UPC only appears in the Packaging Details table, which can list
        // several card-back variants (including later re-releases with a
        // different UPC/assortment) — the first row matches the release
        // already summarized above in the InfoBox, so take that one.
        $upc = $this->packagingValue($xpath, 'UPC');
        $dto->upc = $upc !== '' ? preg_replace('/\s+/', '', $upc) : '';

        $accessories = $this->infoValue($xpath, 'Accessory Details');
        if ($accessories !== '') {
            foreach (explode(',', $accessories) as $item) {
                $clean = trim($item, " \t\n\r.");
                if ($clean !== '') {
                    $dto->items[] = $clean;
                }
            }
        }

        // The main photo gallery is loaded client-side from a separate XML
        // feed (see the finalTilesGallery script config), not present in
        // the fetched HTML at all, so it can't be scraped here. The
        // card-back/packaging photos in the Packaging Details table are
        // real embedded images though, and worth pulling in on their own.
        $dto->images = $this->extractPackagingImages($xpath);

        // Deliberately not scraping a description: the "Release Notes"
        // text on this page is often specifically about a later re-release
        // variant, not the figure this page's own InfoBox summarizes —
        // attaching it here risks pulling in facts about the wrong variant.

        return $dto;
    }

    /**
     * Read a "<p><span class="InfoDetailsEmphasis">Label:</span> value</p>"
     * field from the top summary box.
     */
    private function infoValue(\DOMXPath $xpath, string $label): string
    {
        return $this->spanLabelValue($xpath, 'InfoDetailsEmphasis', $label);
    }

    /**
     * Read a "<p><span class="RDRAdditionalStatsBlack">Label:</span>
     * value</p>" field from the (possibly repeated, once per card-back
     * variant) Packaging Details table — always the first match, which
     * corresponds to the release already summarized in the InfoBox.
     */
    private function packagingValue(\DOMXPath $xpath, string $label): string
    {
        return $this->spanLabelValue($xpath, 'RDRAdditionalStatsBlack', $label);
    }

    private function spanLabelValue(\DOMXPath $xpath, string $spanClass, string $label): string
    {
        $nodes = $xpath->query(
            "(//p[span[@class='{$spanClass}' and translate(normalize-space(.), ':', '') = '{$label}']])[1]"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $p = $nodes->item(0);
        $spanText = '';
        foreach ($p->childNodes as $child) {
            if ($child instanceof \DOMElement
                && strtolower($child->nodeName) === 'span'
                && $child->getAttribute('class') === $spanClass
            ) {
                $spanText = $child->textContent;
                break;
            }
        }

        $full = trim(preg_replace('/\s+/', ' ', $p->textContent));
        $labelText = trim(preg_replace('/\s+/', ' ', $spanText));

        return trim(substr($full, strlen($labelText)));
    }

    private function extractPackagingImages(\DOMXPath $xpath): array
    {
        $images = [];
        $base = 'https://www.jeditemplearchives.com';

        foreach ($xpath->query("//a[contains(@rel,'shadowbox')]") as $node) {
            if ($node instanceof \DOMElement && $node->getAttribute('href')) {
                $images[] = $this->fixRelativeUrl($node->getAttribute('href'), $base);
            }
        }

        return array_values(array_unique($images));
    }
}
