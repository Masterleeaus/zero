<?php

namespace Modules\Performance\Services\JobPerformance;

use Modules\Performance\Entities\JobPerformanceSnapshot;

class PerformanceScoringService
{
    public function score(JobPerformanceSnapshot $snapshot): JobPerformanceSnapshot
    {
        $quality = (new QualityScoreCalculator())->score($snapshot);
        $safety = (new SafetyScoreCalculator())->score($snapshot);
        $timeliness = (new TimelinessScoreCalculator())->score($snapshot);
        $documentation = (new DocumentationScoreCalculator())->score($snapshot);

        $w = config('performance.job_performance.weights', [
            'quality' => 0.35, 'safety' => 0.30, 'timeliness' => 0.20, 'documentation' => 0.15,
        ]);

        $overall = ($quality * $w['quality']) + ($safety * $w['safety']) + ($timeliness * $w['timeliness']) + ($documentation * $w['documentation']);

        $snapshot->quality_score = $quality;
        $snapshot->safety_score = $safety;
        $snapshot->timeliness_score = $timeliness;
        $snapshot->documentation_score = $documentation;
        $snapshot->overall_score = round($overall, 2);
        $snapshot->save();

        return $snapshot;
    }
}
