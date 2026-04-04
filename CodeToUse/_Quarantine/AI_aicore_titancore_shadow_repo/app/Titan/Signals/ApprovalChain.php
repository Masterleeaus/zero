<?php

namespace App\Titan\Signals;

use Illuminate\Support\Facades\DB;

class ApprovalChain
{
    public function __construct(
        protected ?SignalRegistry $registry = null,
        protected ?AuditTrail $auditTrail = null,
    ) {
        $this->registry ??= app(SignalRegistry::class);
        $this->auditTrail ??= app(AuditTrail::class);
    }

    public function determine(array $signal): array
    {
        $approvers = [];
        $severity = $signal['severity'] ?? SignalSeverity::AMBER;
        $amount = (int) data_get($signal, 'payload.amount_cents', 0);
        $rules = $this->registry->approvalRules((string) ($signal['type'] ?? ''));

        if ($severity === SignalSeverity::RED) {
            $approvers = array_merge($approvers, (array) config('titan_signal.approval_roles.red', ['manager']));
        }

        $highAmountThreshold = (int) config('titan_signal.approval_thresholds.amount_cents', 100000);
        if ($amount >= $highAmountThreshold) {
            $approvers = array_merge($approvers, (array) config('titan_signal.approval_roles.high_amount', ['director']));
        }

        if (($signal['type'] ?? '') === 'staff.no_show') {
            $approvers = array_merge($approvers, (array) config('titan_signal.approval_roles.staff_no_show', ['dispatch_lead']));
        }

        if ((bool) data_get($rules, 'always', false)) {
            $approvers = array_merge($approvers, (array) data_get($rules, 'roles', []));
        }

        $approvers = array_values(array_unique(array_filter($approvers)));

        return [
            'approval_chain' => $approvers,
            'requires_approval' => $approvers !== [],
            'next_approver' => $approvers[0] ?? null,
        ];
    }

    public function queue(array $signal): array
    {
        $approval = $this->determine($signal);

        if (! $approval['requires_approval'] || empty($signal['process_id'])) {
            return $approval;
        }

        $processId = (string) $signal['process_id'];

        DB::table('tz_processes')->where('id', $processId)->update([
            'current_state' => 'awaiting-approval',
            'updated_at' => now(),
        ]);

        DB::table('tz_process_states')->insert([
            'process_id' => $processId,
            'from_state' => 'awaiting-processing',
            'to_state' => 'awaiting-approval',
            'metadata' => json_encode([
                'approval_chain' => $approval['approval_chain'],
                'next_approver' => $approval['next_approver'],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        DB::table('tz_approval_queue')->updateOrInsert(
            ['process_id' => $processId],
            [
                'company_id' => $signal['company_id'] ?? null,
                'team_id' => $signal['team_id'] ?? null,
                'user_id' => $signal['user_id'] ?? null,
                'signal_id' => $signal['id'] ?? null,
                'approval_chain' => json_encode($approval['approval_chain'], JSON_UNESCAPED_UNICODE),
                'approved_by' => json_encode([], JSON_UNESCAPED_UNICODE),
                'current_approver' => $approval['next_approver'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->auditTrail->recordEntry($processId, 'approval.queued', [
            'approval_chain' => $approval['approval_chain'],
            'next_approver' => $approval['next_approver'],
        ], $signal['id'] ?? null, $signal['user_id'] ?? null);

        return $approval;
    }

    public function pending(int $companyId, array $filters = []): array
    {
        $query = DB::table('tz_approval_queue')
            ->where('company_id', $companyId)
            ->orderByDesc('created_at');

        foreach (['team_id', 'user_id', 'current_approver', 'status'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $query->limit((int) ($filters['limit'] ?? 50))->get()->map(function ($row) {
            $item = (array) $row;
            $item['approval_chain'] = json_decode($item['approval_chain'] ?? '[]', true) ?: [];
            $item['approved_by'] = json_decode($item['approved_by'] ?? '[]', true) ?: [];
            $item['decision_meta'] = json_decode($item['decision_meta'] ?? '[]', true) ?: [];
            return $item;
        })->all();
    }

    public function decide(string $processId, string $decision, ?string $actor = null, array $meta = []): array
    {
        $queue = DB::table('tz_approval_queue')->where('process_id', $processId)->first();
        if (! $queue) {
            return ['ok' => false, 'message' => 'No approval queue entry found.'];
        }

        $decision = strtolower($decision);
        if (! in_array($decision, ['approved', 'rejected', 'hold'], true)) {
            return ['ok' => false, 'message' => 'Decision must be approved, rejected or hold.'];
        }

        $approvalChain = json_decode($queue->approval_chain ?? '[]', true) ?: [];
        $approvedBy = json_decode($queue->approved_by ?? '[]', true) ?: [];
        if ($actor && $decision === 'approved' && ! in_array($actor, $approvedBy, true)) {
            $approvedBy[] = $actor;
        }

        $remaining = array_values(array_filter($approvalChain, fn ($approver) => ! in_array($approver, $approvedBy, true)));
        $currentApprover = $remaining[0] ?? null;
        $finalApproval = $decision === 'approved' && $remaining === [];
        $status = $decision === 'hold' ? 'hold' : ($finalApproval ? 'approved' : ($decision === 'rejected' ? 'rejected' : 'pending'));

        DB::table('tz_approval_queue')->where('process_id', $processId)->update([
            'status' => $status,
            'approved_by' => json_encode($approvedBy, JSON_UNESCAPED_UNICODE),
            'current_approver' => $decision === 'approved' ? $currentApprover : $actor,
            'decision_meta' => json_encode(array_merge($meta, ['decision' => $decision]), JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
            'decided_at' => in_array($decision, ['approved', 'rejected'], true) ? now() : null,
        ]);

        if ($decision === 'rejected') {
            $toState = 'approval-rejected';
            app(ProcessStateMachine::class)->transitionState($processId, $toState, array_merge($meta, [
                'approval_decision' => $decision,
                'approver' => $actor,
            ]));
        } elseif ($decision === 'hold') {
            $toState = 'awaiting-more-info';
            app(ProcessStateMachine::class)->transitionState($processId, $toState, array_merge($meta, [
                'approval_decision' => $decision,
                'approver' => $actor,
            ]));
        } elseif ($finalApproval) {
            $toState = 'processing';
            app(ProcessStateMachine::class)->transitionState($processId, $toState, array_merge($meta, [
                'approval_decision' => $decision,
                'approver' => $actor,
                'approved_by' => $approvedBy,
            ]));
        } else {
            $toState = 'awaiting-approval';
        }

        $this->auditTrail->recordEntry($processId, 'approval.'.$decision, [
            'approver' => $actor,
            'meta' => $meta,
            'approved_by' => $approvedBy,
            'remaining_approvers' => $remaining,
        ], $queue->signal_id ?? null, null);

        return [
            'ok' => true,
            'process_id' => $processId,
            'decision' => $decision,
            'state' => $toState,
            'current_approver' => $currentApprover,
            'approved_by' => $approvedBy,
            'final' => $finalApproval,
        ];
    }
}
