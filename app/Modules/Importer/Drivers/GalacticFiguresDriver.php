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

        $dto->name = $this->getText($xpath, "//h1") ?: 'Unknown Toy';
        $dto->year = $this->getText($xpath, "//span[@id='yearLabel']");
        $dto->manufacturer = $this->getText($xpath, "//span[@id='manufacturerLabel']");

        $dto->toyLine = $this->getText($xpath, "//a[@id='toyLineLink']");
        if (!$dto->toyLine) {
            $dto->toyLine = $this->getText($xpath, "//span[@id='toyLineLabel']");
        }

        $dto->assortmentSku = $this->getText($xpath, "//span[@id='collectionNumberLabel']");

        // Accessories
        $accessoriesText = $this->getText($xpath, "//*[@id='accessoriesLabel']");
        if ($accessoriesText) {
            foreach (explode(',', $accessoriesText) as $item) {
                $clean = trim($item);
                if ($clean !== '') {
                    $dto->items[] = $clean;
                }
            }
        }

        // Images
        $imgNodes = $xpath->query("//img[@id='mainImage']");
        foreach ($imgNodes as $node) {
            if ($node instanceof \DOMElement) {
                $dto->images[] = $this->fixRelativeUrl(
                    $node->getAttribute('src'),
                    'https://galacticfigures.com'
                );
            }
        }

        return $dto;
    }
}
