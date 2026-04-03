<?php

namespace Modules\BusinessSettingsModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Traits\CompanyScoped;

class CronJob extends Model
{
    use CompanyScoped;
    use HasFactory;

    protected $fillable = ['title','type'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\CronJobFactory::new();
    }
}