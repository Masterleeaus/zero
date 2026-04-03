<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'order',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobStageFactory::new();
    }

    public function applications($filter)
    {
        $application = JobApplication::where('created_by', creatorId())->where('workspace',getActiveWorkSpace())->where('is_archive', 0)->where('stage', $this->id);
        $application->where('created_at', '>=', $filter['start_date']);
        $application->where('created_at', '<=', $filter['end_date']);

        if(!empty($filter['job']))
        {
            $application->where('job', $filter['job']);
        }

        $application = $application->orderBy('order')->get();

        return $application;
    }
}
