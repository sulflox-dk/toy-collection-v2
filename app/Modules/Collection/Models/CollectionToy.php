<?php
namespace App\Modules\Collection\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasAcquisitionStatus;
use App\Modules\Meta\Traits\HasPackagingType;


class CollectionToy extends BaseModel
{
    use HasAcquisitionStatus, HasPackagingType;

    protected static string $table = 'collection_toys';
}