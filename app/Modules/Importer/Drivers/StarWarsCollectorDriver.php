<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class StarWarsCollectorDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Star Wars Collector';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'starwarscollector.com') !== false;
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

        // No numeric figure id anywhere on the page — the URL slug itself
        // (e.g. "vc04-luke-skywalker-bespin-fatigues") is the only stable
        // per-page identifier available.
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segments = explode('/', $path);
        $dto->externalId = end($segments) ?: md5($url);

        // The H1 is "<Figure #>: <Name>" (e.g. "VC04: Luke Skywalker
        // (Bespin Fatigues)") — strip the code prefix so the name matches
        // the plain format the other drivers produce.
        $rawTitle = $this->getText($xpath, "//h1");
        $stripped = preg_replace('/^[A-Za-z0-9]+:\s*/', '', $rawTitle);
        $dto->name = $stripped !== '' ? $stripped : ($rawTitle ?: 'Unknown Toy');

        // The toy line is the first content heading on the page, sitting
        // right below the photo gallery (a later "Navigation" heading in
        // the footer shares the same class, but comes after, so [1] always
        // lands on this one).
        $dto->toyLine = $this->getText($xpath, "(//h2[contains(@class,'elementor-heading-title')])[1]");

        // Breadcrumb reads "<Manufacturer> Star Wars Toy Lines" (e.g.
        // "Hasbro Star Wars Toy Lines").
        $breadcrumbBrand = $this->getText($xpath, "(//p[@id='breadcrumbs']//a)[2]");
        $dto->manufacturer = trim(preg_replace('/\s+Star Wars Toy Lines$/i', '', $breadcrumbBrand));

        $dto->year = $this->labelValue($xpath, 'Year Released');
        $dto->assortmentSku = $this->labelValue($xpath, 'Figure #');

        // UPC isn't reliably available from the other sources — this is
        // this site's real value-add — normalized down to bare digits.
        $upcRaw = $this->labelValue($xpath, 'UPC');
        $dto->upc = $upcRaw !== '' ? preg_replace('/\s+/', '', $upcRaw) : '';

        $dto->items = $this->extractIncludedItems($xpath);

        // Deliberately not scraping a description: name/manufacturer/year/
        // etc. here are the same facts the other drivers already surface,
        // usually with a proper write-up, and this site's own text is just
        // a restatement of the fields above — scraping it here would risk
        // it winning the "first non-empty description wins" merge over an
        // actually descriptive one from another source.

        $dto->images = $this->extractImages($xpath);

        return $dto;
    }

    /**
     * Read a "<p><strong>Label:</strong> value</p>" field — this site's
     * spec fields (Year Released, Figure #, UPC, ...) are all one bare
     * paragraph each, label and value together.
     */
    private function labelValue(\DOMXPath $xpath, string $label): string
    {
        $nodes = $xpath->query(
            "//p[strong[translate(normalize-space(.), ':', '') = '{$label}']]"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $p = $nodes->item(0);
        $strongText = '';
        foreach ($p->childNodes as $child) {
            if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'strong') {
                $strongText = $child->textContent;
                break;
            }
        }

        $full = trim(preg_replace('/\s+/', ' ', $p->textContent));
        $labelText = trim(preg_replace('/\s+/', ' ', $strongText));

        return trim(substr($full, strlen($labelText)));
    }

    /**
     * "Figure Includes:" is its own paragraph where each item is a
     * "<strong>•</strong> Item Name" pair separated by <br> — grab the
     * text node right after each bullet <strong>.
     */
    private function extractIncludedItems(\DOMXPath $xpath): array
    {
        $items = [];
        $bullets = $xpath->query(
            "//p[strong[translate(normalize-space(.), ':', '') = 'Figure Includes']]/strong[normalize-space(.) = '\u{2022}']"
        );

        foreach ($bullets as $bulletNode) {
            $sibling = $xpath->query('following-sibling::text()[1]', $bulletNode);
            if (!$sibling || $sibling->length === 0) {
                continue;
            }
            $text = trim($sibling->item(0)->textContent);
            if ($text !== '') {
                $items[] = $text;
            }
        }

        return $items;
    }

    /**
     * The photo gallery's own lightbox links carry the full-size image URL
     * directly in their href — far more reliable than the <img> tags
     * themselves, which are lazy-loaded behind placeholder data: URIs.
     */
    private function extractImages(\DOMXPath $xpath): array
    {
        $images = [];
        $base = 'https://starwarscollector.com';

        foreach ($xpath->query("//a[contains(@class,'envira-gallery-link')]") as $node) {
            if ($node instanceof \DOMElement && $node->getAttribute('href')) {
                $images[] = $this->fixRelativeUrl($node->getAttribute('href'), $base);
            }
        }

        return array_values(array_unique($images));
    }
}
