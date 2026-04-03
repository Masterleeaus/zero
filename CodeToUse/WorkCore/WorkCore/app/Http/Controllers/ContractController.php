<?php

namespace App\Http\Controllers;

use App\DataTables\ContractsDataTable;
use App\Events\ContractSignedEvent;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Service Agreement\StoreRequest;
use App\Http\Requests\Admin\Service Agreement\UpdateRequest;
use App\Http\Requests\ClientContracts\SignRequest;
use App\Models\BaseModel;
use App\Models\Service Agreement;
use App\Models\ContractSign;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\Currency;
use App\Models\Site;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Helper\UserService;
use App\Models\ClientContact;

class ContractController extends AccountBaseController
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

    public function index(ContractsDataTable $dataTable)
    {
        abort_403(user()->permission('view_contract') == 'none');

        if (!request()->ajax()) {
            $this->sites = Site::allProjects();

            if (in_array('customer', user_roles())) {
                $this->customers = User::customer();
            }
            else {
                $this->customers = User::allClients();
            }

            $this->service agreement = Service Agreement::all();
            $this->contractTypes = ContractType::all();
            $this->contractCounts = Service Agreement::count();
            $this->expiredCounts = Service Agreement::where(DB::raw('DATE(`end_date`)'), '<', now()->format('Y-m-d'))->count();
            $this->aboutToExpireCounts = Service Agreement::where(DB::raw('DATE(`end_date`)'), '>', now()->format('Y-m-d'))
                ->where(DB::raw('DATE(`end_date`)'), '<', now()->timezone($this->company->timezone)->addDays(7)->format('Y-m-d'))
                ->count();
        }

        return $dataTable->render('service agreements.index', $this->data);
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
        abort_403(user()->permission('delete_contract') !== 'all');

        Service Agreement::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;

    }

    public function destroy($id)
    {
        $service agreement = Service Agreement::findOrFail($id);
        $this->deletePermission = user()->permission('delete_contract');
        $userId = UserService::getUserId();
        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $userId == $service agreement->added_by)
            || ($this->deletePermission == 'owned' && $userId == $service agreement->client_id)
            || ($this->deletePermission == 'both' && ($userId == $service agreement->client_id || $userId == $service agreement->added_by)
            )));

        Service Agreement::destroy($id);

        return Reply::success(__('team chat.deleteSuccess'));

    }

    public function create()
    {
        $this->addPermission = user()->permission('add_contract');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->contractId = request('id');
        $this->service agreement = null;

        if ($this->contractId != '') {
            $this->contractTemplate = Service Agreement::findOrFail($this->contractId);
        }

        $this->templates = ContractTemplate::all();
        $this->customers = User::allClients(null, overRidePermission:($this->addPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->sites = Site::all();


        $this->lastContract = Service Agreement::lastContractNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero = '';

        if (strlen($this->lastContract) < $this->invoiceSetting->contract_digit) {
            $condition = $this->invoiceSetting->contract_digit - strlen($this->lastContract);

            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }


        if (is_null($this->contractId)) {
            $this->contractTemplate = request('template') ? ContractTemplate::findOrFail(request('template')) : null;
        }

        $service agreement = new Service Agreement();
        $getCustomFieldGroupsWithFields = $service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = __('app.menu.addContract');

        $this->view = 'service agreements.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service agreements.create', $this->data);

    }

    public function store(StoreRequest $request)
    {
        $service agreement = new Service Agreement();
        $this->storeUpdate($request, $service agreement);

        return Reply::redirect(route('service agreements.index'), __('team chat.recordSaved'));
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_contract');
        $this->service agreement = Service Agreement::with('signature', 'renewHistory', 'renewHistory.renewedBy')
            ->findOrFail($id)
            ->withCustomFields();

        $this->sites = Site::all();
        $userId = UserService::getUserId();

        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && $userId == $this->service agreement->added_by)
            || ($this->editPermission == 'owned' && $userId == $this->service agreement->client_id)
            || ($this->editPermission == 'both' && ($userId == $this->service agreement->client_id || $userId == $this->service agreement->added_by)
            )));

        $this->customers = User::allClients(null, overRidePermission:($this->editPermission == 'all' ? 'all' : null));
        $this->contractTypes = ContractType::all();
        $this->currencies = Currency::all();
        $this->pageTitle = $this->service agreement->contract_number;

        $service agreement = new Service Agreement();

        $getCustomFieldGroupsWithFields = $service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'service agreements.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('service agreements.create', $this->data);

    }

    public function update(UpdateRequest $request, $id)
    {
        $service agreement = Service Agreement::findOrFail($id);
        $this->storeUpdate($request, $service agreement);

        return Reply::redirect(route('service agreements.index'), __('team chat.updateSuccess'));
    }

    private function storeUpdate($request, $service agreement)
    {
        $service agreement->client_id = $request->client_id;
        $service agreement->project_id = $request->project_id;
        $service agreement->subject = $request->subject;
        $service agreement->amount = $request->amount;
        $service agreement->currency_id = $request->currency_id;
        $service agreement->original_amount = $request->amount;
        $service agreement->contract_name = $request->contract_name;
        $service agreement->alternate_address = $request->alternate_address;
        $service agreement->contract_note = $request->note;
        $service agreement->cell = $request->cell;
        $service agreement->office = $request->office;
        $service agreement->city = $request->city;
        $service agreement->state = $request->state;
        $service agreement->country = $request->country;
        $service agreement->postal_code = $request->postal_code;
        $service agreement->contract_type_id = $request->contract_type;
        $service agreement->contract_number = $request->contract_number;
        $service agreement->start_date = companyToYmd($request->start_date);
        $service agreement->original_start_date = companyToYmd($request->start_date);
        $service agreement->end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $service agreement->original_end_date = $request->end_date == null ? $request->end_date : companyToYmd($request->end_date);
        $service agreement->description = trim_editor($request->description);
        $service agreement->contract_detail = trim_editor($request->description);
        $service agreement->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $service agreement->updateCustomFieldData($request->custom_fields_data);
        }

        return $service agreement;
    }

    /**
     * @param int $id
     * @return \Illuminate\Service Agreements\Foundation\Application|\Illuminate\Service Agreements\View\Factory|\Illuminate\Service Agreements\View\View|mixed|void
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_contract');
        $this->addContractPermission = user()->permission('add_contract');
        $this->editContractPermission = user()->permission('edit_contract');
        $this->deleteContractPermission = user()->permission('delete_contract');
        $this->viewDiscussionPermission = $viewDiscussionPermission = user()->permission('view_contract_discussion');
        $this->viewContractFilesPermission = $viewContractFilesPermission = user()->permission('view_contract_files');
        $this->userId = UserService::getUserId();

        $this->cId = $this->id = [];

        if (in_array('customer', user_roles()) && user()->is_client_contact == null) {
            $this->cId = $this->id = ClientContact::where('user_id', user()->id)->pluck('client_id')->toArray();
        }

        $this->service agreement = Service Agreement::with(['signature', 'customer', 'customer.clientDetails', 'files' => function ($q) use ($viewContractFilesPermission) {
            if ($viewContractFilesPermission == 'added') {
                $q->where('added_by', $this->userId);
            }
        }, 'renewHistory', 'renewHistory.renewedBy',
            'discussion' => function ($q) use ($viewDiscussionPermission) {
                if ($viewDiscussionPermission == 'added') {
                    $q->where('contract_discussions.added_by', $this->userId);
                }
            }, 'discussion.user'])->findOrFail($id)->withCustomFields();
        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $this->userId == $this->service agreement->added_by)
            || ($viewPermission == 'owned' && $this->userId == $this->service agreement->client_id)
            || ($viewPermission == 'both' && ($this->userId == $this->service agreement->client_id || $this->userId == $this->service agreement->added_by))
        ));

        $service agreement = new service agreement();

        $getCustomFieldGroupsWithFields = $service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->pageTitle = $this->service agreement->contract_number;

        $tab = request('tab');

        $this->view = match ($tab) {
            'discussion' => 'service agreements.ajax.discussion',
            'files' => 'service agreements.ajax.files',
            'renew' => 'service agreements.ajax.renew',
            default => 'service agreements.ajax.summary',
        };


        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('service agreements.show', $this->data);

    }

    public function download($id)
    {
        $this->service agreement = Service Agreement::findOrFail($id);
        $viewPermission = user()->permission('view_contract');
        $this->service agreement = Service Agreement::with('signature', 'customer', 'customer.clientDetails', 'files')->findOrFail($id)->withCustomFields();
        $userId = UserService::getUserId();

        $getCustomFieldGroupsWithFields = $this->service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $userId == $this->service agreement->added_by)
            || ($viewPermission == 'owned' && $userId == $this->service agreement->client_id)
            || ($viewPermission == 'both' && ($userId == $this->service agreement->client_id || $userId == $this->service agreement->added_by))
        ));


        $pdf = app('dompdf.wrapper');

        $this->company = $this->settings = company();

        $this->invoiceSetting = invoice_setting();

        $pdf->setOption('enable_php', true);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        // $pdf->loadView('service agreements.service agreement-pdf', $this->data);
        $customCss = '<style>
        * { text-transform: none !important; }
        </style>';

        $pdf->loadHTML($customCss . view('service agreements.service agreement-pdf', $this->data)->render());
        $filename = $this->service agreement->contract_number . '-' . __('app.menu.service agreement');

        return $pdf->download($filename . '.pdf');

    }

    public function downloadView($id)
    {
        $this->service agreement = Service Agreement::findOrFail($id)->withCustomFields();
        $pdf = app('dompdf.wrapper');

        $this->company = $this->settings = Company::findOrFail($this->service agreement->company_id);

        $this->invoiceSetting = invoice_setting();

        $getCustomFieldGroupsWithFields = $this->service agreement->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        App::setLocale($this->invoiceSetting->locale ?? 'en');
        Carbon::setLocale($this->invoiceSetting->locale ?? 'en');
        $pdf->loadView('service agreements.service agreement-pdf', $this->data);

        $filename = 'service agreement-' . $this->service agreement->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];
    }

    public function sign(SignRequest $request, $id)
    {
        $this->service agreement = Service Agreement::with('signature')->findOrFail($id);

        if ($this->service agreement && $this->service agreement->signature) {
            return Reply::error(__('team chat.alreadySigned'));
        }

        $sign = new ContractSign();
        $sign->full_name = $request->first_name . ' ' . $request->last_name;
        $sign->contract_id = $this->service agreement->id;
        $sign->email = $request->email;
        $sign->date = now();
        $sign->place = $request->place;
        $imageName = null;

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';
            Files::createDirectoryIfNotExist('service agreement/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/service agreement/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'service agreement/sign', $this->service agreement->company_id);
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'service agreement/sign', 300);
            }
        }

        $sign->signature = $imageName;
        $sign->save();

        event(new ContractSignedEvent($this->service agreement, $sign));

        return Reply::redirect(route('service agreements.show', $this->service agreement->id));
    }

    public function companySign(Request $request)
    {
        $service agreement = Service Agreement::find($request->id);
        $imageName = null;
        $userId = UserService::getUserId();

        if ($request->signature_type == 'signature') {
            $image = $request->signature;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(32) . '.' . 'jpg';

            Files::createDirectoryIfNotExist('service agreement/sign');

            File::put(public_path() . '/' . Files::UPLOAD_FOLDER . '/service agreement/sign/' . $imageName, base64_decode($image));
            Files::uploadLocalFile($imageName, 'service agreement/sign', $service agreement->company_id);
        }
        else {
            if ($request->hasFile('image')) {
                $imageName = Files::uploadLocalOrS3($request->image, 'service agreement/sign', 300);
            }
        }

        $service agreement->company_sign = $imageName;
        $service agreement->sign_date = now();
        $service agreement->sign_by = $userId;
        $service agreement->update();

        return Reply::successWithData(__('team chat.signatureAdded'), ['status' => 'success']);


    }

    public function companiesSign(Request $request, $id)
    {
        $this->service agreement = Service Agreement::find($id);

        return view('service agreements.companysign.sign', $this->data);
    }

    public function projectDetail($id)
    {
        $this->clientDetails = null;

        if ($id != 0) {
            $sites = Site::where('client_id', $id)->get();

            $this->clientDetails = User::where('id', $id)->first();

            $clientInfo = [
                'mobile' => $this->clientDetails->country_phonecode .' '. $this->clientDetails->mobile,
                'office_mobile' => $this->clientDetails->clientDetails->office,
                'city' => $this->clientDetails->clientDetails->city,
                'state' => $this->clientDetails->clientDetails->state,
                'countryName' => $this->clientDetails?->country?->name,
                'postalCode' => $this->clientDetails->clientDetails->postal_code,
            ];


        }
        else {
            $sites = Site::all();
        }

        $options = BaseModel::options($sites, null, 'project_name');

        return Reply::dataOnly(['status' => 'success', 'data' => $options, 'clientDetails' => $clientInfo]);
    }

    public function companySig($id)
    {
        $this->service agreement = Service Agreement::find($id);

        return view('service agreements.companysign.sign', $this->data);
    }

}
