<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasManufacturer;
use App\Modules\Meta\Traits\HasUniverse;
use App\Modules\Meta\Traits\HasToyLine;
use App\Modules\Meta\Traits\HasEntertainmentSource;
use App\Modules\Meta\Traits\HasProductType;

class CatalogToy extends BaseModel
{
    use HasManufacturer, HasUniverse, HasToyLine, HasEntertainmentSource, HasProductType;

    protected static string $table = 'catalog_toys';
}