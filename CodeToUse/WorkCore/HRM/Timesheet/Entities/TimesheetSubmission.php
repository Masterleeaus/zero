<?php

namespace Modules\Timesheet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetSubmission extends Model
{
    use HasFactory;

    protected $table = 'timesheet_submissions';

    protected $fillable = [
        'company_id',
        'workspace_id',
        'user_id',
        'week_start',
        'week_end',
        'status',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'submitter_notes',
        'approver_notes',
        'created_by',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TimesheetSubmissionItem::class, 'submission_id');
    }

    public function scopeForCreator($query)
    {
        return $query->where('created_by', creatorId());
    }
}
