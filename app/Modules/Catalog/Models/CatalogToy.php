<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasManufacturer;
use App\Modules\Meta\Traits\HasUniverse; // Import the Trait

class CatalogToy extends BaseModel
{
    use HasManufacturer, HasUniverse;

    protected static string $table = 'catalog_toys';
}