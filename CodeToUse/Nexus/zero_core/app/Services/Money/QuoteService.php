<?php

declare(strict_types=1);

namespace App\Services\Money;

use App\Models\Money\Quote;
use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function convertToServiceJob(Quote $quote, int $siteId, Authenticatable $user): ServiceJob
    {
        if ($quote->company_id !== $user->company_id) {
            abort(403);
        }

        return DB::transaction(function () use ($quote, $siteId, $user) {
            $site = Site::query()->whereKey($siteId)->firstOrFail();
            if ($site->company_id !== $quote->company_id) {
                abort(403);
            }

            $job = ServiceJob::create([
                'company_id' => $quote->company_id,
                'created_by' => $user->id,
                'site_id'    => $site->id,
                'customer_id'=> $quote->customer_id,
                'quote_id'   => $quote->id,
                'title'      => $quote->title ?: $quote->quote_number,
                'status'     => 'scheduled',
                'notes'      => $quote->notes,
            ]);

            $this->copyChecklistTemplate($quote, $job, $user->company_id, $user->id);

            return $job;
        });
    }

    private function copyChecklistTemplate(Quote $quote, ServiceJob $job, int $companyId, ?int $userId): void
    {
        $items = $quote->checklist_template ?? [];
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            Checklist::create([
                'company_id'     => $companyId,
                'created_by'     => $userId,
                'service_job_id' => $job->id,
                'title'          => $item,
                'is_completed'   => false,
            ]);
        }
    }
}
