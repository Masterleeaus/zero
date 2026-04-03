<?php

namespace App\Observers;

use App\Events\LeadEvent;
use App\Models\Enquiry;
use App\Models\UniversalSearch;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadImported;


class LeadObserver
{

    public function saving(Enquiry $enquiry)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $userID = (!is_null(user())) ? user()->id : null;
            $enquiry->last_updated_by = $userID;
        }

    }

    public function creating(Enquiry $leadContact)
    {
        $leadContact->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            if (request()->has('added_by')) {
                $leadContact->added_by = request('added_by');

            }
            else {
                $userID = (!is_null(user())) ? user()->id : null;
                $leadContact->added_by = $userID;
            }
        }

        if (company()) {
            $leadContact->company_id = company()->id;
        }
    }

    public function created(Enquiry $leadContact)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (!session()->has('is_imported')) {

                event(new LeadEvent($leadContact, 'NewLeadCreated'));
            }else{



                if (session('leads_count') == (session('total_leads'))) {

                    info('check');
                    $admins = User::allAdmins(company()->id);
                    Notification::send($admins, new LeadImported());
                }

            }
        }
    }

    public function deleting(Enquiry $leadContact)
    {
        $notifyData = ['App\Notifications\LeadAgentAssigned', 'App\Notifications\NewDealCreated', 'App\Notifications\NewLeadCreated', 'App\Notifications\LeadImported'];
        \App\Models\Notification::deleteNotification($notifyData, $leadContact->id);
    }

    public function deleted(Enquiry $leadContact)
    {
        UniversalSearch::where('searchable_id', $leadContact->id)->where('module_type', 'enquiry')->delete();
    }

}
