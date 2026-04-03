<?php

namespace Modules\Timesheet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $table = 'timesheets';

    protected $fillable = [
        'company_id',
        'workspace_id',
        'created_by',
        'user_id',
        'project_id',
        'task_id',
        'work_order_id',
        'date',
        'hours',
        'minutes',
        'type',
        'notes',
        'fsm_rate_per_hour',
        'fsm_overtime_multiplier',
        'fsm_cost_total',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'integer',
        'minutes' => 'integer',
        'fsm_rate_per_hour' => 'decimal:2',
        'fsm_overtime_multiplier' => 'decimal:2',
        'fsm_cost_total' => 'decimal:2',
    ];

    public function scopeForCreator($query)
    {
        return $query->where('created_by', creatorId());
    }
}
