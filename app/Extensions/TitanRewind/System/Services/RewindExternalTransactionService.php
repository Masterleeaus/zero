<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Facades\DB;

class RewindExternalTransactionService
{
    public function buildPlan(RewindCase $case, array $rollbackPlan): array
    {
        $paymentPlan = collect($rollbackPlan['payment_plan'] ?? []);

        $linkedPayments = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->where('child_entity_type', 'payments')
            ->get();

        return $linkedPayments->map(function ($payment) use ($paymentPlan) {
            $existing = $paymentPlan->firstWhere('link_id', $payment->id);
            $meta = is_string($payment->meta_json) ? (json_decode($payment->meta_json, true) ?: []) : ($payment->meta_json ?? []);

            return [
                'link_id' => $payment->id,
                'child_process_id' => $payment->child_process_id,
                'entity_id' => $payment->child_entity_id,
                'gateway' => $meta['gateway'] ?? 'external',
                'action' => $existing['action'] ?? 'refund-then-reissue',
                'status' => $meta['external_status'] ?? 'pending-review',
                'details' => $existing['details'] ?? $meta,
            ];
        })->values()->all();
    }

    public function queueReversalActions(RewindCase $case, array $actor): int
    {
        $count = 0;
        $payments = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->where('child_entity_type', 'payments')
            ->get();

        foreach ($payments as $payment) {
            DB::table('titan_rewind_actions')->insert([
                'company_id' => $case->company_id,
                'team_id' => $case->team_id,
                'user_id' => $case->user_id,
                'case_id' => $case->id,
                'fix_id' => null,
                'action_type' => 'payment.reversal.queued',
                'target_type' => 'payments',
                'target_id' => $payment->child_entity_id,
                'before_json' => json_encode(['status' => $payment->status]),
                'after_json' => json_encode([
                    'child_process_id' => $payment->child_process_id,
                    'queued_at' => now()->toIso8601String(),
                    'queued_by' => $actor,
                ]),
                'executed_by_type' => $actor['type'] ?? 'system',
                'executed_by_id' => $actor['id'] ?? null,
                'executed_at' => now(),
                'success' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }
}
