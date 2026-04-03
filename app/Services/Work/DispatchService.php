<?php

namespace App\Services\Work;

use App\Events\Work\JobDispatched;
use App\Events\Work\JobDispatchFailed;
use App\Events\Work\JobReDispatched;
use App\Models\User;
use App\Models\Work\DispatchAssignment;
use App\Models\Work\DispatchQueue;
use App\Models\Work\ServiceJob;
use App\Titan\Signals\AuditTrail;
use App\Titan\Signals\SignalDispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DispatchService
{
    public function __construct(
        protected DispatchConstraintService $constraintService,
        protected SignalDispatcher $signals,
        protected AuditTrail $auditTrail,
    ) {}

    public function allocate(ServiceJob $job): DispatchAssignment
    {
        $candidates = $this->buildCandidatePool($job);
        $constraints = $this->constraintService->loadConstraints($job->company_id);

        if ($candidates->isEmpty()) {
            JobDispatchFailed::dispatch($job);
            $this->emitSignal('dispatch.failed', $job, null, []);
            throw new \RuntimeException("No eligible technicians found for job #{$job->id}");
        }

        $scores = $candidates->map(fn ($tech) => [
            'technician' => $tech,
            'score'      => $this->scoreCandidate($tech, $job, $constraints->all()),
        ])->sortByDesc('score');

        $best = $scores->first();

        return DB::transaction(function () use ($job, $best) {
            DispatchAssignment::where('job_id', $job->id)
                ->where('status', 'pending')
                ->update(['status' => 'superseded']);

            $assignment = DispatchAssignment::create([
                'company_id'       => $job->company_id,
                'job_id'           => $job->id,
                'technician_id'    => $best['technician']->id,
                'assigned_by'      => 'ai',
                'constraint_score' => round($best['score'], 2),
                'assigned_at'      => now(),
                'status'           => 'pending',
            ]);

            $job->update(['assigned_user_id' => $best['technician']->id]);

            JobDispatched::dispatch($job, $assignment);
            $this->emitSignal('dispatch.allocated', $job, $best['technician'], [
                'constraint_score' => $best['score'],
                'technician_id'    => $best['technician']->id,
            ]);

            return $assignment;
        });
    }

    public function scoreCandidate(User $tech, ServiceJob $job, array $constraints): float
    {
        $weights = collect($constraints)->keyBy('constraint_type');
        $score = 0.0;
        $totalWeight = 0.0;

        $skillWeight = $weights->get('skill') ? (float) $weights->get('skill')->weight : 1.0;
        $score += $this->constraintService->evaluateSkillMatch($tech, $job) * $skillWeight;
        $totalWeight += $skillWeight;

        if ($job->premises) {
            $territoryWeight = $weights->get('territory') ? (float) $weights->get('territory')->weight : 1.0;
            $score += $this->constraintService->evaluateTerritoryMatch($tech, $job->premises) * $territoryWeight;
            $totalWeight += $territoryWeight;
        }

        $slaWeight = $weights->get('sla') ? (float) $weights->get('sla')->weight : 1.0;
        $score += $this->constraintService->evaluateSlaUrgency($job) * $slaWeight;
        $totalWeight += $slaWeight;

        return $totalWeight > 0 ? ($score / $totalWeight) : 0.0;
    }

    public function buildCandidatePool(ServiceJob $job): Collection
    {
        return User::where('company_id', $job->company_id)->get();
    }

    public function queueForDispatch(ServiceJob $job): DispatchQueue
    {
        $slaScore = $this->constraintService->evaluateSlaUrgency($job);

        return DispatchQueue::updateOrCreate(
            ['job_id' => $job->id],
            [
                'company_id'     => $job->company_id,
                'priority_score' => $slaScore * 100,
                'queued_at'      => now(),
            ]
        );
    }

    public function confirmAssignment(DispatchAssignment $assignment): void
    {
        $assignment->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->emitSignal('dispatch.confirmed', $assignment->job, $assignment->technician, [
            'assignment_id' => $assignment->id,
        ]);

        $this->auditTrail->recordEntry(
            "dispatch:{$assignment->job_id}",
            'dispatch.confirmed',
            ['assignment_id' => $assignment->id, 'technician_id' => $assignment->technician_id],
            null,
            $assignment->technician_id,
        );
    }

    public function reDispatch(ServiceJob $job, string $reason): DispatchAssignment
    {
        $queue = DispatchQueue::where('job_id', $job->id)->first();

        if ($queue) {
            $queue->increment('attempts');
            $queue->update(['last_attempt_at' => now()]);

            if ($queue->attempts >= 3) {
                $this->auditTrail->recordEntry(
                    "dispatch:{$job->id}",
                    'dispatch.max_attempts_exceeded',
                    ['attempts' => $queue->attempts, 'reason' => $reason],
                    null,
                    null,
                );
            }
        }

        $assignment = $this->allocate($job);

        JobReDispatched::dispatch($job, $assignment, $reason);
        $this->emitSignal('dispatch.reassigned', $job, $assignment->technician, [
            'reason'        => $reason,
            'assignment_id' => $assignment->id,
        ]);

        return $assignment;
    }

    protected function emitSignal(string $type, ServiceJob $job, ?User $tech, array $context): void
    {
        $this->signals->dispatch([
            'type'          => $type,
            'job_id'        => $job->id,
            'company_id'    => $job->company_id,
            'technician_id' => $tech?->id,
            'context'       => $context,
            'emitted_at'    => now()->toIso8601String(),
        ]);
    }
}
