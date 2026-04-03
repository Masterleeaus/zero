<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Entities\WorkflowSetting;

class SettingsRepository
{
    public function getAll(?int $companyId): array
    {
        $rows = WorkflowSetting::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->key] = $r->value;
        }
        return $out;
    }

    public function setMany(?int $companyId, array $data, ?int $userId = null): void
    {
        foreach ($data as $key => $value) {
            WorkflowSetting::updateOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['value' => $value, 'updated_by' => $userId]
            );
        }
    }
}
