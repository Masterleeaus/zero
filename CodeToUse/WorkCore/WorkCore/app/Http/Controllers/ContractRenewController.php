<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\Service Agreement\RenewRequest;
use App\Models\Service Agreement;
use App\Models\ContractRenew;
use App\Models\ContractSign;
use Illuminate\Http\Request;

class ContractRenewController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.service agreements';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('service agreements', $this->user->modules));

            return $next($request);
        });
    }

    public function store(RenewRequest $request)
    {
        $id = $request->contract_id;
        $service agreement = Service Agreement::findOrFail($id);

        $contractRenew = new ContractRenew();
        $contractRenew->amount = $request->amount;
        $contractRenew->renewed_by = $this->user->id;
        $contractRenew->contract_id = $id;
        $contractRenew->start_date = companyToYmd($request->start_date);
        $contractRenew->end_date = companyToYmd($request->end_date);
        $contractRenew->save();

        if (!$request->keep_customer_signature) {
            ContractSign::where('contract_id', $service agreement->id)->delete();
        }

        $service agreement->amount = $contractRenew->amount;
        $service agreement->start_date = $contractRenew->start_date;
        $service agreement->end_date = $contractRenew->end_date;
        $service agreement->save();

        $this->service agreement = Service Agreement::with('signature', 'customer', 'customer.clientDetails', 'files', 'renewHistory', 'renewHistory.renewedBy')->findOrFail($id);

        $view = view('service agreements.renew.renew_history', $this->data)->render();

        return Reply::successWithData(__('team chat.contractRenewSuccess'), ['view' => $view]);
    }

    public function edit($id)
    {
        $this->renew = ContractRenew::findOrFail($id);
        $this->editPermission = user()->permission('edit_contract');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->renew->added_by == user()->id)));

        return view('service agreements.renew.edit', $this->data);

    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(Request $request, $id)
    {
        $contractRenew = ContractRenew::findOrFail($id);
        $contractRenew->amount = $request->amount;
        $contractRenew->start_date = companyToYmd($request->start_date);
        $contractRenew->end_date = companyToYmd($request->end_date);
        $contractRenew->save();

        $this->service agreement = Service Agreement::with('signature', 'customer', 'customer.clientDetails', 'files', 'renewHistory', 'renewHistory.renewedBy')->findOrFail($contractRenew->contract_id);

        $view = view('service agreements.renew.renew_history', $this->data)->render();

        return Reply::successWithData(__('team chat.contractRenewSuccess'), ['view' => $view]);
    }

    /**
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function destroy($id)
    {
        $contractRenew = $this->renew = ContractRenew::findOrFail($id);


        $this->deletePermission = user()->permission('delete_contract');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $this->renew->added_by == user()->id)));
        $findNext = ContractRenew::where('created_at', '>', $contractRenew->created_at)->first();

        if (!$findNext) {
            $findPrevious = ContractRenew::where('created_at', '<', $contractRenew->created_at)->latest()->first();
            $service agreement = Service Agreement::findOrFail($contractRenew->contract_id);

            if ($findPrevious) {
                $service agreement->start_date = $findPrevious->start_date;
                $service agreement->end_date = $findPrevious->end_date;
                $service agreement->amount = $findPrevious->amount;
            }
            else {
                $service agreement->start_date = $service agreement->original_start_date;
                $service agreement->end_date = $service agreement->original_end_date;
                $service agreement->amount = $service agreement->original_amount;
            }

            $service agreement->save();

        }

        ContractRenew::destroy($id);

        $this->service agreement = Service Agreement::with('renewHistory', 'renewHistory.renewedBy')->findOrFail($this->renew->contract_id);
        $view = view('service agreements.renew.renew_history', $this->data)->render();

        return Reply::successWithData(__('team chat.deleteSuccess'), ['view' => $view]);

    }

}
