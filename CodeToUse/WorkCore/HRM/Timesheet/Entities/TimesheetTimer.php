<?php

namespace Modules\Timesheet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetTimer extends Model
{
    use HasFactory;

    protected $table = 'timesheet_timers';

    protected $fillable = [
        'company_id',
        'workspace_id',
        'user_id',
        'project_id',
        'task_id',
        'work_order_id',
        'status',
        'started_at',
        'stopped_at',
        'seconds_total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'seconds_total' => 'integer',
    ];

    public function scopeForCreator($query)
    {
        return $query->where('created_by', creatorId());
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }
}
