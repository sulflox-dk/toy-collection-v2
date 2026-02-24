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

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
        $dto->externalId = $pathInfo['filename'] ?? md5($url);

        // Name
        $dto->name = $this->getText($xpath, "//h1");

        // Year
        $content = $this->getText($xpath, "//body");
        if (preg_match('/Year:.*?(\d{4})/i', $content, $m)) {
            $dto->year = $m[1];
        }

        // Toy Line & Manufacturer (primarily Vintage Kenner)
        $dto->manufacturer = 'Kenner';
        $dto->toyLine = 'Kenner Vintage Star Wars';

        $year = (int) $dto->year;
        if ($year >= 1983) $dto->toyLine = 'Kenner Return of the Jedi';
        if ($year <= 1979) $dto->toyLine = 'Kenner Star Wars';
        if ($year >= 1980 && $year <= 1982) $dto->toyLine = 'Kenner Empire Strikes Back';

        // Main image
        $mainImg = $xpath->query("//img[contains(@class, 'main-image')]");
        if ($mainImg->length === 0) {
            $mainImg = $xpath->query("//div[contains(@class, 'figure-image')]//img");
        }
        if ($mainImg->length > 0 && $mainImg->item(0) instanceof \DOMElement) {
            $dto->images[] = $this->fixRelativeUrl(
                $mainImg->item(0)->getAttribute('src'),
                'https://galacticcollector.com'
            );
        }

        // Accessories (deep scrape sub-pages)
        $accLinks = $xpath->query("//a[contains(@href, '/acc/')]");
        $processedUrls = [];

        foreach ($accLinks as $link) {
            if (!($link instanceof \DOMElement)) continue;
            $accUrl = $this->fixRelativeUrl($link->getAttribute('href'), 'https://galacticcollector.com');

            if (in_array($accUrl, $processedUrls)) continue;
            $processedUrls[] = $accUrl;

            try {
                $subHtml = $this->fetchUrl($accUrl, 10);
            } catch (\RuntimeException $e) {
                continue;
            }

            if (!$subHtml) continue;

            $subXpath = $this->createXPath($subHtml);
            $accName = trim($this->getText($subXpath, "//h1"));

            if ($accName !== '') {
                $dto->items[] = $accName;
            }

            // Accessory image
            $accImgNodes = $subXpath->query("//img");
            foreach ($accImgNodes as $node) {
                if (!($node instanceof \DOMElement)) continue;
                $src = $node->getAttribute('src');
                if (strpos($src, 'images') !== false) {
                    $fullSrc = $this->fixRelativeUrl($src, 'https://galacticcollector.com');
                    if (!in_array($fullSrc, $dto->images)) {
                        $dto->images[] = $fullSrc;
                    }
                    break;
                }
            }
        }

        return $dto;
    }
}
