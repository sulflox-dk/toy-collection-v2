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

        // ID from URL
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $parts = explode('/', $path);
        $dto->externalId = end($parts) ?: md5($url);

        // Name (often "The Vintage Collection - VC01: Dengar")
        $rawTitle = $this->getText($xpath, "//h1");
        if (strpos($rawTitle, ':') !== false) {
            $nameParts = explode(':', $rawTitle);
            $dto->name = trim(end($nameParts));
        } else {
            $dto->name = $rawTitle;
        }
        $dto->name = str_ireplace(['The Vintage Collection - ', 'The Vintage Collection'], '', $dto->name);
        $dto->name = trim($dto->name, ' -');

        // Content block
        $content = $this->getText($xpath, "//div[contains(@class, 'entry-content')]");
        if (empty($content)) {
            $content = $this->getText($xpath, "//body");
        }

        // Year
        if (preg_match('/Year Released:\s*(\d{4})/i', $content, $m)) {
            $dto->year = $m[1];
        }

        // Figure #
        if (preg_match('/Figure #:\s*(.*?)(?=\n|$)/i', $content, $m)) {
            $dto->wave = trim($m[1]);
        }

        // Assortment / SKU
        if (preg_match('/Assortment:\s*(.*?)(?=\n|$)/i', $content, $m)) {
            $dto->assortmentSku = trim($m[1]);
        }

        // Toy Line from URL
        if (stripos($url, 'vintage-collection') !== false) {
            $dto->toyLine = 'The Vintage Collection';
            $dto->manufacturer = 'Hasbro';
        } elseif (stripos($url, 'black-series') !== false) {
            $dto->toyLine = 'The Black Series';
            $dto->manufacturer = 'Hasbro';
        } else {
            $dto->manufacturer = 'Hasbro';
        }

        // Accessories
        if (preg_match('/Figure Includes:(.*?)(?=(Navigation|Home|About Us|$))/is', $content, $m)) {
            $lines = preg_split('/(\\x{2022}|\n)/u', $m[1]);
            foreach ($lines as $line) {
                $clean = trim($line);
                if (strlen($clean) > 2) {
                    $dto->items[] = $clean;
                }
            }
        }

        // Images
        $imgNodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");
        foreach ($imgNodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            $src = $node->getAttribute('src');

            if (strpos($src, 'logo') !== false || strpos($src, 'facebook') !== false) continue;

            $src = $this->fixRelativeUrl($src, 'https://starwarscollector.com');
            $cleanSrc = preg_replace('/-\d+x\d+(?=\.(jpg|png|jpeg))/i', '', $src);

            if (!in_array($cleanSrc, $dto->images)) {
                $dto->images[] = $cleanSrc;
            }
        }

        return $dto;
    }
}
