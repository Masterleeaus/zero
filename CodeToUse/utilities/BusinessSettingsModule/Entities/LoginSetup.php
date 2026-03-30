<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Traits\CompanyScoped;

class LoginSetup extends Model
{
    use CompanyScoped;
    use HasFactory;
    use HasUuid;

    protected $fillable = ['key','value'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\LoginSetupFactory::new();
    }
}