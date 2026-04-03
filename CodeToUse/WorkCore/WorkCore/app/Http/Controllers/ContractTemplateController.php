<?php

namespace App\Http\Controllers;

use App\DataTables\ContractTemplatesDataTable;
use App\Helper\Reply;
use App\Http\Requests\StoreContractTemplate;
use App\Models\Service Agreement;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;

class ContractTemplateController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.contractTemplate';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(ContractTemplatesDataTable $dataTable)
    {
        abort_403(user()->permission('manage_contract_template') == 'none');

        $this->contractTypes = ContractType::all();
        $this->contractCounts = Service Agreement::count();

        return $dataTable->render('service agreement-template.index', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->contractId = request('id');
        $this->service agreement = null;

        if ($this->contractId != '') {
            $this->service agreement = ContractTemplate::findOrFail($this->contractId);
        }

        $this->customers = User::allClients();
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.menu.addContractTemplate');
        $this->view = 'service agreement-template.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service agreement-template.create', $this->data);
    }

    public function store(StoreContractTemplate $request)
    {
        $service agreement = new ContractTemplate();
        $service agreement->subject = $request->subject;
        $service agreement->amount = $request->amount;
        $service agreement->currency_id = $request->currency_id;
        $service agreement->contract_type_id = $request->contract_type;
        $service agreement->description = trim_editor($request->description);
        $service agreement->contract_detail = trim_editor($request->description);
        $service agreement->added_by = user()->id;
        $service agreement->save();

        return Reply::redirect(route('service agreement-template.index'), __('team chat.recordSaved'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->service agreement = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        $this->pageTitle = __('app.menu.contractTemplate');
        $this->view = 'service agreement-template.ajax.overview';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service agreement-template.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $this->service agreement = ContractTemplate::findOrFail($id);
        $this->manageContractTemplatePermission = user()->permission('manage_contract_template');
        abort_403(!in_array($this->manageContractTemplatePermission, ['all', 'added']));

        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();

        $this->pageTitle = __('app.update') . ' ' . __('app.menu.contractTemplate');

        $this->view = 'service agreement-template.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service agreement-template.create', $this->data);
    }

    public function update(StoreContractTemplate $request, $id)
    {
        $service agreement = ContractTemplate::findOrFail($id);
        $service agreement->subject = $request->subject;
        $service agreement->amount = $request->amount;
        $service agreement->currency_id = $request->currency_id;
        $service agreement->contract_type_id = $request->contract_type;
        $service agreement->description = trim_editor($request->description);
        $service agreement->contract_detail = trim_editor($request->description);

        $service agreement->save();

        return Reply::redirect(route('service agreement-template.index'), __('team chat.updateSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        if ($request->action_type == 'delete') {
            $this->deleteRecords($request);

            return Reply::success(__('team chat.deleteSuccess'));
        }

        return Reply::error(__('team chat.selectAction'));
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('manage_contract_template') != 'all' && user()->permission('manage_contract_template') != 'added');

        ContractTemplate::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service agreement = ContractTemplate::findOrFail($id);

        $service agreement->destroy($id);

        return Reply::success(__('team chat.deleteSuccess'));
    }

}
