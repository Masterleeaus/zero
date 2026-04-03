<?php

namespace Modules\Performance\Services\JobPerformance;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class QualityScoreCalculator
{
    public function score(JobPerformanceSnapshot $snapshot): float
    {
        // PASS 2 default scoring placeholders (refine in later pass)
        return 80.0;
    }
}
