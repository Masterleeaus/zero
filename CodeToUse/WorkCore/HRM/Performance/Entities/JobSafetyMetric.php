<?php

namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Performance\Traits\CompanyScoped;

class JobSafetyMetric extends Model
{
    use CompanyScoped;
    protected $fillable = ['snapshot_id','metric_key','label','passed','notes'];

    protected $casts = ['passed' => 'boolean'];

    public function snapshot()
    {
        return $this->belongsTo(JobPerformanceSnapshot::class, 'snapshot_id');
    }
}