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

        // Name from title
        $title = $this->getText($xpath, "//title");
        $dto->name = $this->cleanName($title);

        // Year
        if (preg_match('/Release Date:.*?(\d{4})/i', $html, $m)) {
            $dto->year = $m[1];
        } elseif (strpos($url, 'vintage-star-wars') !== false && preg_match('/Kenner.*?(\d{4})/s', $html, $m)) {
            $dto->year = $m[1];
        }

        // Series / Manufacturer from URL patterns
        if (strpos($url, 'vintage-star-wars') !== false) {
            $dto->toyLine = 'Kenner Vintage Star Wars';
            $dto->manufacturer = 'Kenner';
        } elseif (strpos($url, 'vintage-return-of-the-jedi') !== false) {
            $dto->toyLine = 'Kenner Return of the Jedi';
            $dto->manufacturer = 'Kenner';
        } elseif (strpos($url, 'the-vintage-collection') !== false) {
            $dto->toyLine = 'The Vintage Collection';
            $dto->manufacturer = 'Hasbro';
        } elseif (strpos($url, 'the-black-series') !== false) {
            $dto->toyLine = 'The Black Series';
            $dto->manufacturer = 'Hasbro';
        }

        // SKU / VC Number
        if (preg_match('/(VC\d{2,3})/', $title, $m)) {
            $dto->assortmentSku = $m[1];
            $dto->wave = $m[1];
        }

        // Accessories
        $accessoriesNode = $xpath->query("//b[contains(text(), 'Accessories:')]");
        if ($accessoriesNode->length > 0) {
            $accText = $accessoriesNode->item(0)->nextSibling->textContent ?? '';
            if (empty($accText)) {
                $accText = $accessoriesNode->item(0)->parentNode->textContent ?? '';
                $accText = str_replace('Accessories:', '', $accText);
            }

            foreach (explode(',', $accText) as $item) {
                $clean = trim(strip_tags($item), ". \t\n\r");
                if (strlen($clean) > 2) {
                    $dto->items[] = $clean;
                }
            }
        }

        // Images
        $imgNodes = $xpath->query("//img");
        foreach ($imgNodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            $src = $node->getAttribute('src');

            if (strpos($src, 'banner') !== false || strpos($src, 'button') !== false || strpos($src, 'logo') !== false) {
                continue;
            }

            $src = $this->fixRelativeUrl($src, 'https://www.jeditemplearchives.com');

            if (strpos($src, '_th.') !== false) {
                $src = str_replace('_th.', '.', $src);
            }

            if (!in_array($src, $dto->images)) {
                $dto->images[] = $src;
            }
        }

        return $dto;
    }

    private function cleanName(string $title): string
    {
        $title = str_replace(
            ['Jedi Temple Archives', 'Review', 'Visual Guide', 'Research Droids Reviews'],
            '', $title
        );
        $parts = explode(' - ', $title);
        return trim($parts[0]);
    }
}
