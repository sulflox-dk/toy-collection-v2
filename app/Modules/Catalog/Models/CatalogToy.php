<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasManufacturer;
use App\Modules\Meta\Traits\HasUniverse;
use App\Modules\Meta\Traits\HasToyLine; // Import the Trait

class CatalogToy extends BaseModel
{
    use HasManufacturer, HasUniverse, HasToyLine;

    protected static string $table = 'catalog_toys';
}