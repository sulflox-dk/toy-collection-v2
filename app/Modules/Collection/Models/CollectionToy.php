<?php
namespace App\Modules\Collection\Models;

use App\Kernel\Database\BaseModel;
use App\Modules\Meta\Traits\HasAcquisitionStatus;
use App\Modules\Meta\Traits\HasPackagingType;
use App\Modules\Meta\Traits\HasConditionGrade;
use App\Modules\Meta\Traits\HasGraderTier;
use App\Modules\Meta\Traits\HasGradingCompany;


class CollectionToy extends BaseModel
{
    use HasAcquisitionStatus, HasPackagingType, HasConditionGrade, HasGraderTier, HasGradingCompany;

    protected static string $table = 'collection_toys';
}