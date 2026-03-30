<?php

namespace App\Services\Work;

use App\Models\Work\ServiceAgreement;

class AgreementSchedulerService
{
    public function runForCompany(int $companyId): void
    {
        $agreements = ServiceAgreement::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->whereDate('next_run_at', '<=', now())
            ->get();

        foreach ($agreements as $agreement) {
            $agreement->createJob();
            $agreement->scheduleNext();
        }
    }
}
