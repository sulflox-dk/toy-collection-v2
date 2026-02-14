<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasManufacturer; // Import the Trait

class CatalogToy extends BaseModel
{
    use HasManufacturer; // Apply the Trait

    protected static string $table = 'catalog_toys';
}