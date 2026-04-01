<?php

namespace App\Services\BusinessSuite;

class DraftService
{
    /**
     * Promote an approved work draft into the appropriate host domain record.
     *
     * @param  array<string, mixed>  $draft
     * @return mixed  The newly created host-domain model instance.
     *
     * @throws \InvalidArgumentException  When an unknown draft_type is provided.
     */
    public function promote(array $draft, int $companyId, int $userId): mixed
    {
        return match ($draft['draft_type']) {
            'quote'       => \App\Models\Money\Quote::create([
                'company_id'  => $companyId,
                'created_by'  => $userId,
                'customer_id' => $draft['contact_id'] ?? null,
                'title'       => $draft['title'],
                'status'      => 'draft',
                'currency'    => $draft['currency'] ?? 'AUD',
            ]),
            'booking'     => \App\Models\Work\Site::create([
                'company_id'   => $companyId,
                'created_by'   => $userId,
                'customer_id'  => $draft['contact_id'] ?? null,
                'name'         => $draft['title'],
                'status'       => 'pending',
                'scheduled_at' => $draft['scheduled_at'] ?? null,
            ]),
            'service_job' => \App\Models\Work\ServiceJob::create([
                'company_id' => $companyId,
                'created_by' => $userId,
                'title'      => $draft['title'],
                'status'     => 'pending',
            ]),
            'invoice'     => \App\Models\Money\Invoice::create([
                'company_id'  => $companyId,
                'created_by'  => $userId,
                'customer_id' => $draft['contact_id'] ?? null,
                'title'       => $draft['title'],
                'status'      => 'draft',
                'currency'    => $draft['currency'] ?? 'AUD',
            ]),
            default => throw new \InvalidArgumentException("Unknown draft type: {$draft['draft_type']}"),
        };
    }
}
