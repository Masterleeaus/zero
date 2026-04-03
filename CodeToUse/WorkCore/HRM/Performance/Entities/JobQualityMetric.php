<?php

namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Performance\Traits\CompanyScoped;

class JobQualityMetric extends Model
{
    use CompanyScoped;
    protected $fillable = ['snapshot_id','metric_key','label','value','unit','notes'];

    protected $casts = ['value' => 'decimal:2'];

    public function snapshot()
    {
        return $this->belongsTo(JobPerformanceSnapshot::class, 'snapshot_id');
    }
}