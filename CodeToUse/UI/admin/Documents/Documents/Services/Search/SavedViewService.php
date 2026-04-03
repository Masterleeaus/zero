<?php

namespace Modules\Documents\Services\Search;

use Modules\Documents\Entities\DocumentSavedView;

class SavedViewService
{
    public function systemDefaults(?int $companyId, ?int $userId): void
    {
        // Create system views once per company (idempotent)
        $defaults = [
            ['name' => 'My Drafts', 'filters' => ['status' => 'draft'], 'is_system' => true],
            ['name' => 'Needs Review', 'filters' => ['status' => 'review'], 'is_system' => true],
            ['name' => 'Approved', 'filters' => ['status' => 'approved'], 'is_system' => true],
            ['name' => 'SWMS Approved', 'filters' => ['type' => 'swms', 'status' => 'approved'], 'is_system' => true],
        ];

        foreach ($defaults as $d) {
            DocumentSavedView::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'user_id' => null,
                    'name' => $d['name'],
                    'is_system' => true,
                ],
                [
                    'filters' => $d['filters'],
                ]
            );
        }
    }
}
