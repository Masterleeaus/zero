<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\FieldServiceProjectCompleted;
use App\Events\Work\FieldServiceProjectCreated;
use App\Events\Work\FieldServiceProjectJobLinked;
use App\Events\Work\FieldServiceProjectUpdated;
use App\Events\Work\FieldServiceProjectVisitLinked;
use App\Models\Work\FieldServiceProject;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlanVisit;
use Illuminate\Support\Facades\DB;

class FieldServiceProjectService
{
    public function createProject(array $data): FieldServiceProject
    {
        return DB::transaction(function () use ($data) {
            $project = FieldServiceProject::create($data);
            event(new FieldServiceProjectCreated($project));
            return $project;
        });
    }

    public function updateProject(FieldServiceProject $project, array $data): FieldServiceProject
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);
            event(new FieldServiceProjectUpdated($project));
            return $project->fresh();
        });
    }

    public function linkJob(FieldServiceProject $project, ServiceJob $job): ServiceJob
    {
        $job->update(['project_id' => $project->id]);
        event(new FieldServiceProjectJobLinked($project, $job));
        return $job->fresh();
    }

    public function linkVisit(FieldServiceProject $project, ServicePlanVisit $visit): ServicePlanVisit
    {
        $visit->update(['project_id' => $project->id]);
        event(new FieldServiceProjectVisitLinked($project, $visit));
        return $visit->fresh();
    }

    public function createJobForProject(FieldServiceProject $project, array $jobData): ServiceJob
    {
        $jobData['project_id']  = $project->id;
        $jobData['customer_id'] = $jobData['customer_id'] ?? $project->customer_id;
        $jobData['premises_id'] = $jobData['premises_id'] ?? $project->premises_id;
        $jobData['team_id']     = $jobData['team_id'] ?? $project->team_id;

        $job = ServiceJob::create($jobData);
        event(new FieldServiceProjectJobLinked($project, $job));
        return $job;
    }

    public function checkAndCompleteProject(FieldServiceProject $project): bool
    {
        $allDone = $project->serviceJobs()
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->doesntExist();

        if ($allDone && $project->isActive() && $project->serviceJobs()->exists()) {
            $project->update([
                'status'     => 'completed',
                'actual_end' => now()->toDateString(),
            ]);
            event(new FieldServiceProjectCompleted($project));
            return true;
        }

        return false;
    }
}
