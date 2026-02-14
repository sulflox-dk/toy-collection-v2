<?php
namespace App\Modules\Meta\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasManufacturer; // Import the Trait

class ToyLine extends BaseModel
{
    use HasManufacturer; // Apply the Trait

    protected static string $table = 'meta_toy_lines';
}