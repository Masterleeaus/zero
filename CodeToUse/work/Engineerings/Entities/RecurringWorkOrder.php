<?php

namespace Modules\Engineerings\Entities;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Notifications\Notifiable;
use Modules\Assets\Entities\Assets;
use Modules\Complaint\Entities\Complaint;
use Modules\Engineerings\Entities\WorkOrder;
use Modules\Engineerings\Entities\WorkRequest;
use Modules\Units\Entities\Unit;

class RecurringWorkOrder extends BaseModel
{
    use Notifiable, HasCompany;

    protected $table = 'workorders_recurring';
    protected $dates = ['issue_date', 'next_schedule_date'];

    const MODULE_NAME = 'Engineerings';
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
        return $this->hasMany(WorkOrder::class, 'workorder_recurring_id');
    }

    public function wr()
    {
        return $this->belongsTo(WorkRequest::class, 'workrequest_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function assets()
    {
        return $this->belongsTo(Assets::class, 'assets_id');
    }

    public function getIssueOnAttribute()
    {
        if (is_null($this->issue_date)) {
            return '';
        }

        return Carbon::parse($this->issue_date)->format('d F, Y');
    }
}
