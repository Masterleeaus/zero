<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Events\Crm\ServiceJobCreatedFromDeal;
use App\Events\Crm\ServiceJobCreatedFromEnquiry;
use App\Events\Crm\ServiceJobClosedUpdatesCrm;
use App\Models\Crm\Deal;
use App\Models\Crm\Enquiry;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CrmServiceJobService
 *
 * Module 6 (fieldservice_crm) — CRM → ServiceJob conversion and lifecycle bridge.
 *
 * Handles:
 *   - Creating a ServiceJob from an Enquiry (lead)
 *   - Creating a ServiceJob from a Deal (opportunity)
 *   - Notifying CRM when a ServiceJob is closed
 *
 * All operations are company_id scoped.
 */
class CrmServiceJobService
{
    /**
     * Create a ServiceJob from a CRM enquiry (lead).
     *
     * The enquiry status is updated to 'converted' and a back-reference is stored
     * on the job. A ServiceJobCreatedFromEnquiry event is dispatched for listeners.
     *
     * @param  Enquiry               $enquiry
     * @param  array<string, mixed>  $overrides  Optional field overrides for the job.
     * @return ServiceJob
     */
    public function createJobFromEnquiry(Enquiry $enquiry, array $overrides = []): ServiceJob
    {
        return DB::transaction(function () use ($enquiry, $overrides): ServiceJob {
            $job = ServiceJob::create(array_merge([
                'company_id'  => $enquiry->company_id,
                'created_by'  => Auth::id() ?? $enquiry->created_by,
                'customer_id' => $enquiry->customer_id,
                'enquiry_id'  => $enquiry->id,
                'title'       => $enquiry->name,
                'status'      => 'scheduled',
                'priority'    => 'normal',
            ], $overrides));

            $enquiry->update(['status' => 'converted']);

            ServiceJobCreatedFromEnquiry::dispatch($job, $enquiry);

            return $job;
        });
    }

    /**
     * Create a ServiceJob from a CRM deal (opportunity).
     *
     * The deal status is updated to 'won' and a back-reference is stored on the job.
     * A ServiceJobCreatedFromDeal event is dispatched for listeners.
     *
     * @param  Deal                  $deal
     * @param  array<string, mixed>  $overrides  Optional field overrides for the job.
     * @return ServiceJob
     */
    public function createJobFromDeal(Deal $deal, array $overrides = []): ServiceJob
    {
        return DB::transaction(function () use ($deal, $overrides): ServiceJob {
            $job = ServiceJob::create(array_merge([
                'company_id'  => $deal->company_id,
                'created_by'  => Auth::id() ?? $deal->created_by,
                'customer_id' => $deal->customer_id,
                'deal_id'     => $deal->id,
                'title'       => $deal->title,
                'status'      => 'scheduled',
                'priority'    => 'normal',
            ], $overrides));

            $deal->update(['status' => 'won']);

            ServiceJobCreatedFromDeal::dispatch($job, $deal);

            return $job;
        });
    }

    /**
     * Notify the CRM when a ServiceJob is closed (completed or cancelled).
     *
     * Updates the linked enquiry/deal status and dispatches the
     * ServiceJobClosedUpdatesCrm event for downstream listeners.
     *
     * @param ServiceJob $job
     */
    public function notifyCrmJobClosed(ServiceJob $job): void
    {
        if ($job->enquiry_id && $job->enquiry) {
            $newStatus = $job->status === 'completed' ? 'closed' : 'cancelled';
            $job->enquiry->update(['status' => $newStatus]);
        }

        if ($job->deal_id && $job->deal) {
            $newStatus = $job->status === 'completed' ? 'won' : 'lost';
            $job->deal->update(['status' => $newStatus]);
        }

        ServiceJobClosedUpdatesCrm::dispatch($job);
    }
}
