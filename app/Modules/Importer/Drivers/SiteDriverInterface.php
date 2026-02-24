<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;

interface SiteDriverInterface
{
    public function getSiteName(): string;
    public function canHandle(string $url): bool;
    public function isOverviewPage(string $url): bool;
    public function parseSinglePage(string $url): ScrapedToyDTO;

    /**
     * @return string[] Array of detail-page URLs
     */
    public function parseOverviewPage(string $url): array;
}
