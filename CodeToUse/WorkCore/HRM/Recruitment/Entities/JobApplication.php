<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job',
        'name',
        'email',
        'phone',
        'profile',
        'resume',
        'cover_letter',
        'dob',
        'gender',
        'country',
        'state',
        'city',
        'stage',
        'order',
        'skill',
        'rating',
        'is_archive',
        'custom_question',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobApplicationFactory::new();
    }

    public function jobs()
    {
        return $this->hasOne(Job::class, 'id', 'job');
    }
    
    public function stages()
    {
        return $this->hasOne(JobStage::class, 'id', 'stage');
    }
}
