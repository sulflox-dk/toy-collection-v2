<?php
namespace App\Modules\Importer\Models;

class ScrapedToyDTO
{
    public string $externalId = '';
    public string $externalUrl = '';
    public string $name = '';
    public string $description = '';
    public string $manufacturer = '';
    public string $year = '';
    public string $toyLine = '';
    public string $wave = '';
    public string $assortmentSku = '';
    public array $items = [];
    public array $images = [];

    public function toArray(): array
    {
        return [
            'externalId' => $this->externalId,
            'externalUrl' => $this->externalUrl,
            'name' => $this->name,
            'description' => $this->description,
            'manufacturer' => $this->manufacturer,
            'year' => $this->year,
            'toyLine' => $this->toyLine,
            'wave' => $this->wave,
            'assortmentSku' => $this->assortmentSku,
            'items' => $this->items,
            'images' => $this->images,
        ];
    }
}
