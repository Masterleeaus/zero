<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Deal;
use Illuminate\Http\Request;
use App\Http\Requests\Gdpr\RemoveLeadRequest;
use App\Http\Requests\GdprLead\UpdateRequest;
use App\Models\PurposeConsent;
use App\Models\PurposeConsentLead;
use App\Models\RemovalRequestLead;

class PublicLeadGdprController extends AccountBaseController
{

    public function updateLead(UpdateRequest $request, $id)
    {
        $gdprSetting = gdpr_setting();

        if(!$gdprSetting->public_lead_edit) {
            return Reply::error('team chat.unAuthorisedUser');
        }

        $enquiry = Deal::whereRaw('md5(id) = ?', $id)->firstOrFail();
        $enquiry->company_name = $request->company_name;
        $enquiry->website = $request->website;
        $enquiry->address = $request->address;
        $enquiry->client_name = $request->client_name;
        $enquiry->client_email = $request->client_email;
        $enquiry->mobile = $request->mobile;
        $enquiry->note = trim_editor($request->note);
        $enquiry->status_id = $request->status;
        $enquiry->source_id = $request->source;
        $enquiry->next_follow_up = $request->next_follow_up;
        $enquiry->save();

        return Reply::success('team chat.updateSuccess');
    }

    public function consent($hash)
    {
        $this->pageTitle = 'modules.gdpr.consent';
        $this->gdprSetting = gdpr_setting();

        abort_if(!$this->gdprSetting->consent_leads, 404);

        $enquiry = Deal::where('hash', $hash)->firstOrFail();
        $this->consents = PurposeConsent::with(['enquiry' => function($query) use($enquiry) {
            $query->where('lead_id', $enquiry->id)
                ->orderByDesc('created_at');
        }])->get();

        $this->enquiry = $enquiry;

        return view('public-gdpr.consent', $this->data);
    }

    public function updateConsent(Request $request, $id)
    {
        $enquiry = Deal::whereRaw('md5(id) = ?', $id)->firstOrFail();

        $allConsents = $request->has('consent_customer') ? $request->consent_customer : [];

        foreach ($allConsents as $allConsentId => $allConsentStatus)
        {
            $newConsentLead = new PurposeConsentLead();
            $newConsentLead->lead_id = $enquiry->id;
            $newConsentLead->purpose_consent_id = $allConsentId;
            $newConsentLead->status = $allConsentStatus;
            $newConsentLead->ip = $request->ip();
            $newConsentLead->save();
        }

        return Reply::success('team chat.updateSuccess');
    }

    public function removeLeadRequest(RemoveLeadRequest $request)
    {
        $gdprSetting = gdpr_setting();

        if(!$gdprSetting->lead_removal_public_form) {
            return Reply::error('team chat.unAuthorisedUser');
        }

        $enquiry = Deal::findOrFail($request->lead_id);

        $removal = new RemovalRequestLead();
        $removal->lead_id = $request->lead_id;
        $removal->name = $enquiry->company_name;
        $removal->description = trim_editor($request->description);
        $removal->save();

        return Reply::success('modules.gdpr.removalRequestSuccess');
    }

}
