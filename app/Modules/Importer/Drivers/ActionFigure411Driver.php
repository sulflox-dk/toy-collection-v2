<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class ActionFigure411Driver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'Action Figure 411';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'actionfigure411.com') !== false;
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

        // ID from URL
        $parts = explode('/', rtrim($url, '/'));
        $lastPart = end($parts);
        $dto->externalId = str_replace('.php', '', $lastPart);

        // Name
        $rawName = $this->getText($xpath, "//h1");
        $dto->name = trim(str_replace(['Star Wars ', 'Action Figure'], '', $rawName));

        // Year
        if (preg_match('/Year:.*?(\d{4})/is', $html, $m)) {
            $dto->year = $m[1];
        }

        // Series
        if (preg_match('/Series:.*?>(.*?)<(\/a|br|\/div)/is', $html, $m)) {
            $dto->toyLine = trim(strip_tags($m[1]));
        }

        // Wave
        if (preg_match('/Wave:.*?>(.*?)<(\/a|br|\/div)/is', $html, $m)) {
            $dto->wave = trim(strip_tags($m[1]));
        }

        // Manufacturer
        if (preg_match('/Manufacturer:.*?>(.*?)<(\/a|br|\/div)/is', $html, $m)) {
            $dto->manufacturer = trim(strip_tags($m[1]));
        }
        if (empty($dto->manufacturer)) {
            if (stripos($html, 'Hasbro') !== false) {
                $dto->manufacturer = 'Hasbro';
            } elseif (stripos($html, 'Kenner') !== false) {
                $dto->manufacturer = 'Kenner';
            }
        }

        // UPC as SKU
        if (preg_match('/UPC:.*?(\d{10,13})/is', $html, $m)) {
            $dto->assortmentSku = $m[1];
        }

        // Images
        $imgNodes = $xpath->query("//img");
        foreach ($imgNodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            $src = $node->getAttribute('src');

            if (strpos($src, 'actionfigure411-logo') !== false || strpos($src, 'facebook') !== false) {
                continue;
            }

            if (strpos($src, '/images/') !== false || strpos($src, 'actionFigures') !== false) {
                $src = $this->fixRelativeUrl($src, 'https://www.actionfigure411.com');
                if (!in_array($src, $dto->images)) {
                    $dto->images[] = $src;
                }
            }
        }

        return $dto;
    }
}
