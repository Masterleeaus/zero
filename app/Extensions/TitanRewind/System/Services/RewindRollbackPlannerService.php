<?php

namespace App\Extensions\TitanRewind\System\Services;

use App\Extensions\TitanRewind\System\Models\RewindCase;
use Illuminate\Support\Facades\DB;

class RewindRollbackPlannerService
{
    public function plan(RewindCase $case): array
    {
        $links = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->orderBy('depth')
            ->orderBy('id')
            ->get();

        $stages = [];
        foreach ($links as $link) {
            $stageKey = 'depth_' . (int) ($link->depth ?? 1);
            $stages[$stageKey] ??= [
                'depth' => (int) ($link->depth ?? 1),
                'reuse' => [],
                'reissue' => [],
                'notify' => [],
            ];

            $item = [
                'link_id' => $link->id,
                'child_process_id' => $link->child_process_id,
                'entity_type' => $link->child_entity_type,
                'entity_id' => $link->child_entity_id,
                'status' => $link->status,
                'action_required' => $link->action_required,
            ];

            if ((bool) ($link->must_reissue ?? false)) {
                $stages[$stageKey]['reissue'][] = $item;
                $stages[$stageKey]['notify'][] = [
                    'type' => 'reissue-required',
                    'entity_type' => $link->child_entity_type,
                    'child_process_id' => $link->child_process_id,
                ];
            } else {
                $stages[$stageKey]['reuse'][] = $item;
            }
        }

        $counts = [
            'stages' => count($stages),
            'reuse' => array_sum(array_map(fn ($stage) => count($stage['reuse']), $stages)),
            'reissue' => array_sum(array_map(fn ($stage) => count($stage['reissue']), $stages)),
        ];

        $refunds = $this->paymentPlan($case);

        return [
            'case_id' => $case->id,
            'root' => [
                'process_id' => $case->process_id,
                'entity_type' => $case->entity_type,
                'entity_id' => $case->entity_id,
            ],
            'stages' => array_values($stages),
            'counts' => $counts,
            'payment_plan' => $refunds,
        ];
    }

    public function paymentPlan(RewindCase $case): array
    {
        $paymentLinks = DB::table('tz_rewind_links')
            ->where('company_id', $case->company_id)
            ->where('case_id', $case->id)
            ->where('child_entity_type', 'payments')
            ->get();

        return $paymentLinks->map(function ($link) {
            $meta = is_string($link->meta_json) ? (json_decode($link->meta_json, true) ?: []) : ($link->meta_json ?? []);
            return [
                'link_id' => $link->id,
                'child_process_id' => $link->child_process_id,
                'entity_id' => $link->child_entity_id,
                'action' => 'refund-then-reissue',
                'reason' => 'payment rewind touches external transaction flow',
                'details' => $meta,
            ];
        })->values()->all();
    }
}
