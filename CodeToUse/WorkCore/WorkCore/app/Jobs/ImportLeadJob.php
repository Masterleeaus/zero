<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\Enquiry;
use App\Models\LeadPipeline;
use App\Models\LeadSource;
use App\Models\PipelineStage;
use App\Models\User;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Service Agreements\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportLeadJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $leadCount = Session::get('total_leads');
        $leadCount++;
        
        Session::put('total_leads', $leadCount);

        if ($this->isColumnExists('name')) {

            if ($this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {
                $enquiry = Enquiry::where('client_email', $this->getColumnValue('email'))->where('company_id', $this->company?->id)->first();
                $user = User::where('email', $this->getColumnValue('email'))->first();

                if ($enquiry || $user) {

                    $this->failJobWithMessage(__('team chat.duplicateEntryForEmail') . $this->getColumnValue('email'));

                    return;
                }
            }
            else {
                $this->failJob(__('team chat.invalidData'));

                return;
            }

            DB::beginTransaction();
            Session::put('is_imported', true);
            Session::put('is_deal', true);
            try {

                $leadSource = null;

                if ($this->isColumnExists('source')) {
                    $leadSource = LeadSource::where('type', $this->getColumnValue('source'))->where('company_id', $this->company?->id)->first();
                }

                $enquiry = new Enquiry();
                $enquiry->company_id = $this->company?->id;
                $enquiry->client_name = $this->getColumnValue('name');
                $enquiry->client_email = $this->isColumnExists('email') && filter_var($this->getColumnValue('email'), FILTER_VALIDATE_EMAIL) ? $this->getColumnValue('email') : null;
                $enquiry->note = $this->isColumnExists('note') ? $this->getColumnValue('note') : null;
                $enquiry->company_name = $this->isColumnExists('company_name') ? $this->getColumnValue('company_name') : null;
                $enquiry->website = $this->isColumnExists('company_website') ? $this->getColumnValue('company_website') : null;
                $enquiry->mobile = $this->isColumnExists('mobile') ? $this->getColumnValue('mobile') : null;
                $enquiry->office = $this->isColumnExists('company_phone') ? $this->getColumnValue('company_phone') : null;
                $enquiry->country = $this->isColumnExists('country') ? $this->getColumnValue('country') : null;
                $enquiry->state = $this->isColumnExists('state') ? $this->getColumnValue('state') : null;
                $enquiry->city = $this->isColumnExists('city') ? $this->getColumnValue('city') : null;
                $enquiry->postal_code = $this->isColumnExists('postal_code') ? $this->getColumnValue('postal_code') : null;
                $enquiry->address = $this->isColumnExists('address') ? $this->getColumnValue('address') : null;
                $enquiry->source_id = $leadSource?->id;
                
                // Match owner email and set lead_owner
                if ($this->isColumnExists('owner_email')) {
                    $ownerEmail = $this->getColumnValue('owner_email');
                    
                    if (!empty($ownerEmail)) {
                        // Trim whitespace from email
                        $ownerEmail = trim($ownerEmail);
                        
                        if ($this->isEmailValid($ownerEmail)) {
                            $owner = User::where('email', $ownerEmail)
                                ->where('company_id', $this->company?->id)
                                ->first();
                            
                            if ($owner) {
                                $enquiry->lead_owner = $owner->id;
                            }
                        }
                    }
                }
                
                $enquiry->created_at = $this->isColumnExists('created_at') ? Carbon::parse($this->getColumnValue('created_at')) : now();

                $enquiries = Session::get('enquiries', []);

                $enquiries[] = [
                    'lead_name'  => $enquiry->client_name,
                    'email' => $enquiry->client_email,
                    'deal_name' => $enquiry->client_name,
                ];

              
                Session::put('enquiries', $enquiries);

                $enquiry->save();

                $leadPipeline = LeadPipeline::where('default', '1')->where('company_id', $enquiry->company_id)->first();
                $leadStage = PipelineStage::where('default', '1')->where('lead_pipeline_id', $leadPipeline->id)->where('company_id', $enquiry->company_id)->first();

                $deal = new Deal();
                $deal->company_id = $enquiry->company_id;
                $deal->lead_id = $enquiry->id;
                $deal->name = $enquiry->client_name ?? '';
                $deal->lead_pipeline_id = $leadPipeline->id;
                $deal->pipeline_stage_id = $leadStage->id;
                $deal->value = 0;
                $deal->currency_id = $enquiry->company?->currency_id;
                $deal->save();

                // Log search
                $this->logSearchEntry($enquiry->id, $enquiry->client_name, 'enquiry-contact', 'enquiry', $enquiry->company_id);

                if (!is_null($enquiry->client_email)) {
                    $this->logSearchEntry($enquiry->id, $enquiry->client_email, 'enquiry-contact', 'enquiry', $enquiry->company_id);
                }

                if (!is_null($enquiry->company_name)) {
                    $this->logSearchEntry($enquiry->id, $enquiry->company_name, 'enquiry-contact', 'enquiry', $enquiry->company_id);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        }
        else {
            $this->failJob(__('team chat.invalidData'));
        }


    }

}

