<?php

namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Performance\Traits\CompanyScoped;

class JobPerformanceSnapshot extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'objective_id','user_id','supervisor_id','project_id','job_id','jobsite_id','work_order_id',
        'overall_score','quality_score','safety_score','timeliness_score','documentation_score',
        'callback_count','customer_rating','status','signed_off_at'
    ];

    protected $casts = [
        'signed_off_at' => 'datetime',
        'overall_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'safety_score' => 'decimal:2',
        'timeliness_score' => 'decimal:2',
        'documentation_score' => 'decimal:2',
        'customer_rating' => 'decimal:2',
    ];

    public function objective()
    {
        return $this->belongsTo(Objective::class, 'objective_id');
    }

    public function qualityMetrics()
    {
        return $this->hasMany(JobQualityMetric::class, 'snapshot_id');
    }

    public function safetyMetrics()
    {
        return $this->hasMany(JobSafetyMetric::class, 'snapshot_id');
    }
}