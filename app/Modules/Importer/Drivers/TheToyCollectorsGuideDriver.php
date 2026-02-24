<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

class TheToyCollectorsGuideDriver extends AbstractSiteDriver
{
    public function getSiteName(): string
    {
        return 'The Toy Collectors Guide';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'thetoycollectorsguide.com') !== false;
    }

    public function isOverviewPage(string $url): bool
    {
        return strpos($url, '#item-') === false;
    }

    public function parseOverviewPage(string $url): array
    {
        $html = $this->fetchUrl($url);
        $xpath = $this->createXPath($html);

        $urls = [];
        $nodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");

        $count = 0;
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            $width = $node->getAttribute('width');
            $src = $node->getAttribute('src');

            if (($width && (int) $width < 100) || strpos($src, 'logo') !== false) continue;

            $urls[] = $url . '#item-' . $count;
            $count++;
        }

        return $urls;
    }

    public function parseSinglePage(string $url): ScrapedToyDTO
    {
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $index = $fragment ? (int) str_replace('item-', '', $fragment) : 0;

        $cleanUrl = strtok($url, '#');
        $html = $this->fetchUrl($cleanUrl);
        $xpath = $this->createXPath($html);

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        // Collect valid image nodes
        $nodes = $xpath->query("//div[contains(@class, 'entry-content')]//img");
        $validNodes = [];
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            $w = $node->getAttribute('width');
            if (($w && (int) $w < 100) || strpos($node->getAttribute('src'), 'logo') !== false) continue;
            $validNodes[] = $node;
        }

        if (empty($validNodes)) {
            throw new \RuntimeException("Could not find any valid item on page for index $index");
        }

        $targetNode = $validNodes[$index] ?? $validNodes[0];

        // Image
        $src = $targetNode->getAttribute('src');
        $cleanSrc = preg_replace('/-\d+x\d+(?=\.(jpg|png|jpeg))/i', '', $src);
        $dto->images[] = $cleanSrc;

        // Name from alt text or filename
        $altText = $targetNode->getAttribute('alt');
        if (!empty($altText)) {
            $dto->name = $altText;
        } else {
            $filename = pathinfo($src, PATHINFO_FILENAME);
            $filename = str_replace(['-', '_'], ' ', $filename);
            $filename = preg_replace('/\d+x\d+/', '', $filename);
            $dto->name = ucwords(trim($filename));
        }

        if (empty($dto->name)) {
            $dto->name = "Unknown Item #" . ($index + 1);
        }

        $dto->externalId = md5($cleanUrl . '|' . $index . '|' . $dto->name);

        // Toy Line from page title
        $pageTitle = $this->getText($xpath, "//h1");
        $dto->toyLine = trim(str_replace('The Toy Collectors Guide', '', $pageTitle));

        // Year
        if (preg_match('/(19|20)\d{2}/', $dto->toyLine, $m)) {
            $dto->year = $m[0];
        } else {
            $content = $this->getText($xpath, "//div[contains(@class, 'entry-content')]");
            if (preg_match('/(19|20)\d{2}/', $content, $m)) {
                $dto->year = $m[0];
            }
        }

        $dto->name = trim(str_replace(['Image', 'Photo'], '', $dto->name));

        return $dto;
    }
}
