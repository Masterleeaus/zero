<?php

namespace Modules\Performance\Services\JobPerformance;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class DocumentationScoreCalculator
{
    public function score(JobPerformanceSnapshot $snapshot): float
    {
        // PASS 2 default scoring placeholders (refine in later pass)
        return 70.0;
    }
}
