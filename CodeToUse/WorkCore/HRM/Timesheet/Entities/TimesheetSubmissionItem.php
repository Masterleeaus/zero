<?php

namespace Modules\Timesheet\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetSubmissionItem extends Model
{
    use HasFactory;

    protected $table = 'timesheet_submission_items';

    protected $fillable = [
        'submission_id',
        'timesheet_id',
    ];

    public function submission()
    {
        return $this->belongsTo(TimesheetSubmission::class, 'submission_id');
    }

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id');
    }
}
