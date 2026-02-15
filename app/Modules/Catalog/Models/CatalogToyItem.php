<?php
namespace App\Modules\Catalog\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasSubject;

class CatalogToyItem extends BaseModel
{
    use HasSubject;

    protected static string $table = 'catalog_toy_items';
}