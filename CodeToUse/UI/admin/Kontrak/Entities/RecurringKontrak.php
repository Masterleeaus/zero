<?php
namespace Modules\Kontrak\Entities;

use Carbon\Carbon;
use App\Models\BaseModel;
use App\Models\Contract;
use App\Traits\HasCompany;
use Illuminate\Notifications\Notifiable;
use Modules\Units\Entities\Unit;

class RecurringKontrak extends BaseModel
{
    use Notifiable, HasCompany;

    protected $table = 'contract_detail';
    protected $dates = ['issue_date', 'next_schedule_date'];

    const MODULE_NAME = 'kontrak';
    const ROTATION_COLOR = [
        'daily' => 'success',
        'weekly' => 'info',
        'bi-weekly' => 'warning',
        'monthly' => 'secondary',
        'quarterly' => 'light',
        'half-yearly' => 'dark',
        'annually' => 'success',
    ];

    public function recurrings()
    {
        return $this->hasMany(Contract::class, 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function getIssueOnAttribute()
    {
        if (is_null($this->issue_date)) {
            return '';
        }

        return Carbon::parse($this->issue_date)->format('d F, Y');
    }
}
