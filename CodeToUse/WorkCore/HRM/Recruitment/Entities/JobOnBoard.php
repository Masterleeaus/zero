<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOnBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'application',
        'joining_date',
        'status',
        'convert_to_employee',
        'workspace',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobOnBoardFactory::new();
    }

    public function applications()
    {
        return $this->hasOne(JobApplication::class, 'id', 'application');
    }

    public static $status = [
        '' => 'Select Status',
        'pending' => 'Pending',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
    ];

    public static $job_type = [
        '' => 'Select Job Type',
        'full time' => 'Full Time',
        'part time' => 'Part Time',

    ];

    public static $salary_duration = [
        '' => 'Select Salary Duration',
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
    ];
}
