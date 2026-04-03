<?php

namespace Modules\Timesheet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetCompanySetting extends Model
{
    use HasFactory;

    protected $table = 'timesheet_company_settings';

    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];
}
