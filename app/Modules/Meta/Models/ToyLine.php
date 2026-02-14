<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasUniverse;
use App\Modules\Meta\Traits\HasManufacturer;

class ToyLine extends BaseModel
{
    use HasManufacturer, HasUniverse;

    protected static string $table = 'meta_toy_lines';
}