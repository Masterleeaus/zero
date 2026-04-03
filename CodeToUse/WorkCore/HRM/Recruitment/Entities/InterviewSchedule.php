<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterviewSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate',
        'employee',
        'date',
        'time',
        'comment',
        'employee_response',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\InterviewScheduleFactory::new();
    }

    public function applications()
    {
       return $this->hasOne(JobApplication::class,'id','candidate');
    }

    public function users()
    {
        return $this->hasOne(\Modules\Hrm\Entities\Employee::class, 'id', 'employee');
    }
}
