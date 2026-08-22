<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class GalacticCollectorDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Galactic Collector';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'galacticcollector.com') !== false;
    }

    public function isOverviewPage(string $url): bool
    {
        return strpos($url, '/fig/') === false;
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

        // Every image src on this site is document-relative (e.g.
        // "../img/acc/x.png"), so resolve against the page's own URL, not
        // just the bare domain.
        $base = $url;

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
        $dto->externalId = $pathInfo['filename'] ?? md5($url);

        // The H1 reads "Action Figure Name: <Name> (<Year>)" — pull the
        // name and year apart instead of using the whole thing as-is.
        $rawH1 = $this->getText($xpath, "//h1");
        if (preg_match('/^Action Figure Name:\s*(.+?)\s*\((\d{4})\)\s*$/', $rawH1, $m)) {
            $dto->name = $m[1];
            $dto->year = $m[2];
        } else {
            $dto->name = preg_replace('/^Action Figure Name:\s*/', '', $rawH1) ?: 'Unknown Toy';
        }

        // The manufacturer is only reliably stated in the page's own
        // Product JSON-LD block (brand.name) — there's no visible on-page
        // label for it. No toy line/wave/assortment number is stated
        // anywhere on the page in a structured, parseable way, so those
        // are deliberately left unset rather than guessed.
        foreach ($xpath->query("//script[@type='application/ld+json']") as $node) {
            $data = json_decode($node->textContent, true);
            if (is_array($data) && ($data['@type'] ?? '') === 'Product') {
                $dto->manufacturer = $data['brand']['name'] ?? '';
                break;
            }
        }

        // "Collector's Notes" is genuine descriptive prose about the figure
        // itself (not a specific card-back/re-release variant), unlike the
        // structured spec fields the other drivers already cover — a real
        // value-add worth carrying over as the description.
        $notes = $xpath->query("//h2[a[@name='buy']]/following-sibling::div[contains(@class,'tbox')][1]");
        if ($notes->length > 0) {
            $text = $notes->item(0)->textContent;
            $text = preg_replace('/[ \t]+/', ' ', $text);
            $text = preg_replace('/\s*\n\s*\n\s*/', "\n\n", $text);
            $dto->description = trim($text);
        }

        // Main product photo.
        $mainImg = $xpath->query("//div[contains(@class,'img200')]//img");
        if ($mainImg->length > 0 && $mainImg->item(0) instanceof \DOMElement) {
            $dto->images[] = $this->fixRelativeUrl($mainImg->item(0)->getAttribute('src'), $base);
        }

        // Accessories are listed right on the page with their own photo
        // already inline — no need to follow each one to its own detail
        // page. Each ".tbox-item" is "<img> <accessory name>".
        foreach ($xpath->query("//h2[a[@name='acc']]/following-sibling::div[contains(@class,'tbox')][1]//div[contains(@class,'tbox-item')]") as $itemNode) {
            $nameText = trim(preg_replace('/\s+/', ' ', $itemNode->textContent));
            if ($nameText === '') {
                continue;
            }
            $dto->items[] = $nameText;

            $imgNodes = $xpath->query(".//img", $itemNode);
            if ($imgNodes->length > 0 && $imgNodes->item(0) instanceof \DOMElement) {
                $src = $imgNodes->item(0)->getAttribute('src');
                if ($src !== '') {
                    $dto->itemImages[$nameText] = $this->fixRelativeUrl($src, $base);
                }
            }
        }

        return $dto;
    }
}
