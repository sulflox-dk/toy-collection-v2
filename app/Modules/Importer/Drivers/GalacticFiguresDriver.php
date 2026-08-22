<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class GalacticFiguresDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Galactic Figures';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'galacticfigures.com') !== false;
    }

    public function isOverviewPage(string $url): bool
    {
        return strpos($url, 'type=toyline') !== false;
    }

    public function parseOverviewPage(string $url): array
    {
        $html = $this->fetchUrl($url);
        $xpath = $this->createXPath($html);

        $urls = [];
        $nodes = $xpath->query("//a[contains(@href, 'figureDetails.aspx')]");

        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) {
                $href = $node->getAttribute('href');
                $urls[] = $this->fixRelativeUrl($href, 'https://galacticfigures.com');
            }
        }

        return array_values(array_unique($urls));
    }

    public function parseSinglePage(string $url): ScrapedToyDTO
    {
        $html = $this->fetchUrl($url);
        $xpath = $this->createXPath($html);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $params);
        $dto->externalId = $params['id'] ?? md5($url);

        // The full figure name (with variant, e.g. "Luke Skywalker (Bespin Fatigues)")
        // lives in the last breadcrumb crumb, not the <h1> (which is just the bare
        // character name). Fall back to the <h1> if the breadcrumb isn't there.
        $dto->name = $this->getText($xpath, "//ol[contains(@class,'BreadCrumb')]/li[last()]/strong")
            ?: $this->getText($xpath, "//h1")
            ?: 'Unknown Toy';

        // Manufacturer and toy line are breadcrumb links, not labeled spans.
        $dto->manufacturer = $this->getText($xpath, "//ol[contains(@class,'BreadCrumb')]/li/a[contains(@href,'/brands.aspx')]");
        $dto->toyLine = $this->getText($xpath, "//ol[contains(@class,'BreadCrumb')]/li/a[contains(@href,'type=toyline')]");

        // The structured "Details" list is far more reliable than the loosely
        // formatted header line, so pull year/accessories from there.
        $dto->year = $this->detailValue($xpath, 'Released') ?: $this->detailValue($xpath, 'Year Imprinted');

        // The assortment/figure-number code (e.g. "VC4") sits as bare text between
        // the toy-line link and the year in the header, with no element of its own
        // to target. This site doesn't separately expose a wave number anywhere on
        // the page (confirmed against Action Figure 411's page for the same figure,
        // which lists "VC 04" as its Figure Number and "1" as a distinct Wave) —
        // wave stays unset here rather than reusing this value for the wrong field.
        $dto->assortmentSku = $this->extractAssortmentCodeFromHeader($xpath);

        // Accessories: "Accessory Details: 1 Blaster, 1 Lightsaber Hilt, ..."
        $accessoriesText = $this->detailValue($xpath, 'Accessory Details');
        if ($accessoriesText) {
            foreach (explode(',', $accessoriesText) as $item) {
                $clean = trim(preg_replace('/^1\s+/', '', trim($item)));
                if ($clean !== '') {
                    $dto->items[] = $clean;
                }
            }
        }

        // The site's own write-up doubles nicely as a description — it's usually
        // several paragraphs, not just the first one.
        $dto->description = $this->extractDescription($xpath);

        $dto->images = $this->extractImages($xpath);

        return $dto;
    }

    /**
     * Read a "<strong>Label:</strong> value" row out of the Details list
     * (ul.gf-sci-bullets), matching the label loosely (site sometimes wraps
     * it across lines).
     */
    private function detailValue(\DOMXPath $xpath, string $label): string
    {
        $nodes = $xpath->query(
            "//ul[contains(@class,'gf-sci-bullets')]//li[strong[contains(normalize-space(.), '{$label}:')]]"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $li = $nodes->item(0);
        $strongText = '';
        foreach ($li->childNodes as $child) {
            if ($child instanceof \DOMElement && strtolower($child->nodeName) === 'strong') {
                $strongText = $child->textContent;
                break;
            }
        }

        $full = trim(preg_replace('/\s+/', ' ', $li->textContent));
        $labelText = trim(preg_replace('/\s+/', ' ', $strongText));

        return trim(substr($full, strlen($labelText)));
    }

    /**
     * The site's write-up is usually several <p> tags long — join all of
     * them, not just the first, so the full piece comes through.
     */
    private function extractDescription(\DOMXPath $xpath): string
    {
        $paragraphs = [];
        foreach ($xpath->query("//div[contains(@class,'figure-more-info-content')]/p") as $node) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
            if ($text !== '') {
                $paragraphs[] = $text;
            }
        }
        return implode("\n\n", $paragraphs);
    }

    /**
     * The header line looks like:
     *   [toy-line link] VC4 2010 [eBay link]
     * "VC4" and "2010" are one bare text node between the toy-line link and
     * the following <br>, with no markup of their own. Grab that text node
     * and take its first non-year token as the assortment/figure-number code.
     */
    private function extractAssortmentCodeFromHeader(\DOMXPath $xpath): string
    {
        $nodes = $xpath->query(
            "//span[contains(@class,'figureHeader-meta')]/a[contains(@href,'type=toyline')]/following-sibling::text()[1]"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $tokens = preg_split('/\s+/', trim($nodes->item(0)->textContent), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tokens as $token) {
            if (!preg_match('/^\d{4}$/', $token)) {
                return $token;
            }
        }

        return '';
    }

    /**
     * Every real product photo: the main image, the thumbnail strip (each
     * carries the full-size URL in data-big), and the high-res gallery —
     * excluding the "members only" placeholder thumbnail, which isn't a
     * real photo of the figure.
     */
    private function extractImages(\DOMXPath $xpath): array
    {
        $images = [];
        $base = 'https://galacticfigures.com';

        foreach ($xpath->query("//img[@id='mainImage']") as $node) {
            if ($node instanceof \DOMElement && $node->getAttribute('src')) {
                $images[] = $this->fixRelativeUrl($node->getAttribute('src'), $base);
            }
        }

        foreach ($xpath->query("//img[contains(@class,'figure-details-img-thumbnail')]") as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            if (stripos($node->getAttribute('class'), 'member-only') !== false) {
                continue;
            }
            if (stripos($node->getAttribute('alt'), 'members only') !== false) {
                continue;
            }
            $src = $node->getAttribute('data-big') ?: $node->getAttribute('src');
            if ($src && stripos($src, 'membersonly') === false) {
                $images[] = $this->fixRelativeUrl($src, $base);
            }
        }

        foreach ($xpath->query("//a[contains(@class,'figure-detail-highres-link')]") as $node) {
            if ($node instanceof \DOMElement && $node->getAttribute('href')) {
                $images[] = $this->fixRelativeUrl($node->getAttribute('href'), $base);
            }
        }

        return array_values(array_unique($images));
    }
}
