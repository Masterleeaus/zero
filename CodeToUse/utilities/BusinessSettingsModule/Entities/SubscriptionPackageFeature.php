<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Traits\CompanyScoped;

class SubscriptionPackageFeature extends Model
{
    use CompanyScoped;
    use HasFactory;
    use HasUuid;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\SubscriptionPackageFeatureFactory::new();
    }
}