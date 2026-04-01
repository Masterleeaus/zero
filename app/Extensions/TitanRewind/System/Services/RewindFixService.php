<?php

namespace App\Extensions\TitanRewind\System\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Extensions\TitanRewind\System\Models\RewindAction;
use App\Extensions\TitanRewind\System\Models\RewindCase;
use App\Extensions\TitanRewind\System\Models\RewindFix;

class RewindFixService
{
    public function proposeFix(RewindCase $case, array $proposal, array $proposedBy, bool $requiresConfirmation = true): RewindFix
    {
        return RewindFix::query()->create([
            'company_id' => $case->company_id,'team_id' => $case->team_id,'user_id' => $case->user_id,'case_id' => $case->id,
            'fix_type' => $proposal['fix_type'] ?? 'manual','proposed_by_type' => $proposedBy['type'] ?? 'user','proposed_by_id' => $proposedBy['id'] ?? null,
            'requires_confirmation' => $requiresConfirmation,'status' => $requiresConfirmation ? 'proposed' : 'confirmed','proposal_json' => $proposal,
            'confirm_token' => (string) Str::uuid(),
        ]);
    }

    public function confirmFix(RewindFix $fix, array $actor): RewindFix
    {
        if ($fix->status !== 'proposed') return $fix;
        $fix->status = 'confirmed';
        $fix->confirmed_at = now();
        $fix->confirmed_by_type = $actor['type'] ?? 'user';
        $fix->confirmed_by_id = $actor['id'] ?? null;
        $fix->save();
        return $fix;
    }

    public function applyFix(RewindFix $fix, array $actor): RewindFix
    {
        if ($fix->requires_confirmation && $fix->status !== 'confirmed') throw new \RuntimeException('Fix must be confirmed before applying.');
        if (in_array($fix->status, ['applied', 'failed'], true)) return $fix;

        return DB::transaction(function () use ($fix, $actor) {
            $fix->status = 'applying';
            $fix->save();
            try {
                $result = $this->applyByType($fix);
                $fix->status = 'applied';
                $fix->applied_at = now();
                $fix->applied_by_type = $actor['type'] ?? 'user';
                $fix->applied_by_id = $actor['id'] ?? null;
                $fix->result_json = $result;
                $fix->save();
                RewindAction::query()->create([
                    'company_id' => $fix->company_id,'team_id' => $fix->team_id,'user_id' => $fix->user_id,'case_id' => $fix->case_id,
                    'fix_id' => $fix->id,'action_type' => 'apply_fix','target_type' => $result['target_type'] ?? null,'target_id' => $result['target_id'] ?? null,
                    'before_json' => $result['before'] ?? null,'after_json' => $result['after'] ?? null,'executed_by_type' => $actor['type'] ?? 'system',
                    'executed_by_id' => $actor['id'] ?? null,'executed_at' => now(),'success' => true,
                ]);
            } catch (\Throwable $e) {
                $fix->status = 'failed';
                $fix->error_text = $e->getMessage();
                $fix->save();
                RewindAction::query()->create([
                    'company_id' => $fix->company_id,'team_id' => $fix->team_id,'user_id' => $fix->user_id,'case_id' => $fix->case_id,
                    'fix_id' => $fix->id,'action_type' => 'apply_fix','executed_by_type' => $actor['type'] ?? 'system','executed_by_id' => $actor['id'] ?? null,
                    'executed_at' => now(),'success' => false,'error_text' => $e->getMessage(),
                ]);
            }
            return $fix;
        });
    }

    public function processQueue(int $limit = 50): int
    {
        $count = 0;
        foreach (RewindFix::query()->where('status', 'confirmed')->orderBy('id')->limit($limit)->get() as $fix) {
            $this->applyFix($fix, ['type' => 'system', 'id' => null]);
            $count++;
        }
        return $count;
    }

    private function applyByType(RewindFix $fix): array
    {
        $proposal = $fix->proposal_json ?? [];
        if ($fix->fix_type !== 'metadata_update') throw new \RuntimeException("Unknown fix_type: {$fix->fix_type}");
        $targetTable = $proposal['target_table'] ?? null;
        $targetId = $proposal['target_id'] ?? null;
        $metaKey = $proposal['meta_key'] ?? null;
        $metaValue = $proposal['meta_value'] ?? null;
        if (!$targetTable || !$targetId || !$metaKey) throw new \InvalidArgumentException('metadata_update missing required fields.');
        if (!in_array($targetTable, config('titan-rewind.allowlisted_target_tables', []), true)) throw new \RuntimeException('Target table not allowlisted.');
        $row = DB::table($targetTable)->where('id', $targetId)->where('company_id', $fix->company_id)->first();
        if (!$row) throw new \RuntimeException('Target row not found.');
        $before = json_decode($row->meta_json ?? '[]', true) ?: [];
        $after = $before;
        $after[$metaKey] = $metaValue;
        DB::table($targetTable)->where('id', $targetId)->where('company_id', $fix->company_id)->update(['meta_json' => json_encode($after)]);
        return ['target_type' => $targetTable,'target_id' => $targetId,'before' => ['meta_json' => $before],'after' => ['meta_json' => $after]];
    }
}
