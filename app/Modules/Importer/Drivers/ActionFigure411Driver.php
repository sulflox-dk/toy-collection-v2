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

        // The site's own numeric figure id (also used in its own links, e.g.
        // add-remove-item.php?m=2265) is more stable than the full slug.
        $dto->externalId = preg_match('/-(\d+)\.php/', $url, $m)
            ? $m[1]
            : str_replace('.php', '', basename(parse_url($url, PHP_URL_PATH) ?: ''));

        // The full figure name (with variant) lives in the last breadcrumb
        // crumb, trailing a " - [Group Name]" suffix to strip off. The <h1>
        // is the same text but without a clean way to drop the toy-line
        // prefix, so prefer the breadcrumb.
        $breadcrumbName = $this->getText($xpath, "//li[contains(@class,'breadcrumb-item') and contains(@class,'active')]");
        if ($breadcrumbName !== '') {
            $dto->name = trim(preg_replace('/\s*-\s*\[.*\]\s*$/s', '', $breadcrumbName));
        }
        if ($dto->name === '') {
            $dto->name = $this->getText($xpath, "//h1") ?: 'Unknown Toy';
        }

        // "Figure Number" (e.g. "VC 04") is this site's assortment/collection
        // number; "Wave" is a separate, purely numeric field — they are NOT
        // the same thing here, unlike some other sites.
        $dto->assortmentSku = $this->labelValue($xpath, 'Figure Number');
        $dto->wave = $this->labelValue($xpath, 'Wave');
        $dto->year = $this->labelValue($xpath, 'Year');
        $dto->toyLine = $this->labelValue($xpath, 'Series');
        $dto->manufacturer = $this->labelValue($xpath, 'Manufacturer');

        $dto->description = $this->getText($xpath, "//meta[@property='og:description']/@content")
            ?: $this->getText($xpath, "//meta[@name='description']/@content");

        // Only this figure's own photo — the page also carries thumbnails
        // for "Similar Items", the "Other figures in group" carousel, and
        // eBay listing photos, none of which belong to this toy.
        $mainImage = $xpath->query("//div[contains(@class,'img-container-bigger-icon')][1]//a[@data-fancybox]");
        if ($mainImage && $mainImage->length > 0 && $mainImage->item(0) instanceof \DOMElement) {
            $href = $mainImage->item(0)->getAttribute('href');
            if ($href) {
                $dto->images[] = $this->fixRelativeUrl($href, 'https://www.actionfigure411.com');
            }
        }
        if (empty($dto->images)) {
            $ogImage = $this->getText($xpath, "//meta[@property='og:image']/@content");
            if ($ogImage) {
                $dto->images[] = $ogImage;
            }
        }

        return $dto;
    }

    /**
     * Read a "<b>Label</b>: value" or "<b>Label:</b> value" field out of the
     * figure's info list — the colon sometimes sits inside the <b>, sometimes
     * outside, and the field is sometimes wrapped in an inline <h2>, so match
     * on the label text with any colon stripped and just take the next text
     * node in document order.
     */
    private function labelValue(\DOMXPath $xpath, string $label): string
    {
        $nodes = $xpath->query(
            "//li[contains(@class,'list-group-item')]//b[translate(normalize-space(.), ':', '') = '{$label}']"
        );
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $sibling = $xpath->query('following-sibling::text()[1]', $nodes->item(0));
        if (!$sibling || $sibling->length === 0) {
            return '';
        }

        return trim($sibling->item(0)->textContent, " :\t\n\r\0\x0B");
    }
}
