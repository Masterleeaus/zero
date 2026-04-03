<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\LeadCustomForm;
use Illuminate\Http\Request;

class LeadCustomFormController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.enquiry.leadForm';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('enquiries', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {

        $manageLeadFormPermission = user()->permission('manage_lead_custom_forms');
        abort_403($manageLeadFormPermission != 'all');

        $this->leadFormFields = LeadCustomForm::get();

        return view('enquiries.enquiry-form.index', $this->data);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $updateData = [];
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        
        if ($request->has('required')) {
            $updateData['required'] = $request->required;
        }
        
        LeadCustomForm::where('id', $id)->update($updateData);

        return Reply::success(__('team chat.updateSuccess'));
    }

    /**
     * sort fields order
     *
     * @return \Illuminate\Http\Response
     */
    public function sortFields()
    {
        $sortedValues = request('sortedValues');

        foreach ($sortedValues as $key => $value) {
            LeadCustomForm::where('id', $value)->update(['field_order' => $key + 1]);
        }

        return Reply::dataOnly([]);
    }
}
