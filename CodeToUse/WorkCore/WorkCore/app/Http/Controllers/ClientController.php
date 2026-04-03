<?php

namespace App\Http\Controllers;

use App\Models\ProjectStatusSetting;
use App\Models\Role;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Site;
use App\Models\BaseModel;
use App\Models\ClientNote;
use App\Scopes\ActiveScope;
use App\Traits\ImportExcel;
use App\Models\Notification;
use App\Models\ContractType;
use App\Models\UserAuth;
use Illuminate\Http\Request;
use App\Imports\ClientImport;
use App\Jobs\ImportClientJob;
use App\Models\ClientDetails;
use App\Models\ClientCategory;
use App\Models\PurposeConsent;
use App\Models\LanguageSetting;
use App\Models\UniversalSearch;
use App\Models\ClientSubCategory;
use App\Models\PurposeConsentUser;
use Illuminate\Support\Facades\DB;
use App\DataTables\TicketDataTable;
use App\DataTables\ClientsDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\EstimatesDataTable;
use App\DataTables\ClientGDPRDataTable;
use App\DataTables\ClientNotesDataTable;
use App\DataTables\CreditNotesDataTable;
use App\DataTables\ClientContactsDataTable;
use App\DataTables\OrdersDataTable;
use App\Enums\Salutation;
use App\Events\NewUserEvent;
use App\Http\Requests\Admin\Cleaner\ImportRequest;
use App\Http\Requests\Admin\Customer\StoreClientRequest;
use App\Http\Requests\Gdpr\SaveConsentUserDataRequest;
use App\Http\Requests\Admin\Customer\UpdateClientRequest;
use App\Http\Requests\Admin\Cleaner\ImportProcessRequest;
use App\Models\ClientContact;
use App\Models\Enquiry;
use App\Scopes\CompanyScope;
use App\Traits\EmployeeActivityTrait;

class ClientController extends AccountBaseController
{

    use ImportExcel;
    use EmployeeActivityTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customers';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('customers', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * customer list
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ClientsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_clients');
        $this->addClientPermission = user()->permission('add_clients');

        abort_403(!in_array($viewPermission, ['all', 'added', 'both']));

        if (!request()->ajax()) {
            $this->customers = User::allClients(active:false);
            $this->subcategories = ClientSubCategory::all();
            $this->categories = ClientCategory::all();
            $this->sites = Site::all();
            $this->service agreements = ContractType::all();
            $this->countries = countries();
            $this->totalClients = count($this->customers);
        }

        return $dataTable->render('customers.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create($leadID = null)
    {
        $this->addPermission = user()->permission('add_clients');

        abort_403(!in_array($this->addPermission, User::ALL_ADDED_BOTH));

        if ($leadID) {
            $this->leadDetail = Enquiry::findOrFail($leadID);
        }

        if (request('enquiry') != '') {
            $this->leadId = request('enquiry');
            $this->type = 'enquiry';
            $this->enquiry = Enquiry::findOrFail($this->leadId);
        }

        if ($this->addPermission == 'all') {
            $this->cleaners = User::allEmployees(null,true);
        }

        $this->pageTitle = __('app.addClient');
        $this->countries = countries();
        $this->categories = ClientCategory::all();
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();

        $customer = new ClientDetails();
        $getCustomFieldGroupsWithFields = $customer->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'customers.ajax.create';

        if (request()->ajax()) {
            if (request('quick-form') == 1) {
                return view('customers.ajax.quick_create', $this->data);
            }

            return $this->returnAjax($this->view);
        }


        return view('customers.create', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function store(StoreClientRequest $request)
    {

        DB::beginTransaction();

        $data = $request->all();
        unset($data['country']);
        unset($data['is_client_contact']);

        if ($request->email != '') {
            $userAuth = UserAuth::createUserAuthCredentials($request->email, $request->password);
            $data['user_auth_id'] = $userAuth->id;
        }

        $data['country_id'] = $request->country;
        $data['name'] = $request->name;

        if($request->is_client_contact){
            $data['email_notifications'] = 1;
        }else {
            $data['email_notifications'] = $request->sendMail == 'yes' ? 1 : 0;
        }

        $data['gender'] = $request->gender ?? null;
        $data['locale'] = $request->locale ?? 'en';

        if ($request->has('telegram_user_id')) {
            $data['telegram_user_id'] = $request->telegram_user_id;
        }

        if ($request->hasFile('image')) {
            $data['image'] = Files::uploadLocalOrS3($request->image, 'avatar', 300);
        }

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = Files::uploadLocalOrS3($request->company_logo, 'customer-logo', 300);
        }

        if($request->has('is_client_contact')){
            $data['email_notifications'] = 1;
        }

        $user = User::create($data);
        $user->clientDetails()->create($data);
        $client_id = $user->id;

        if($request->has('is_client_contact')){
            $clientContact = new ClientContact();
            $clientContact->user_id = $request->is_client_contact;
            $clientContact->contact_name = $request->name;
            $clientContact->phone = $request->mobile;
            $clientContact->email = $request->email;
            $clientContact->title = $request->title;
            $clientContact->address = $request->address;
            $clientContact->added_by = user()->id;
            $clientContact->last_updated_by = user()->id;
            $clientContact->client_id = $client_id;
            $clientContact->save();

            $user->is_client_contact = $clientContact->id;
            $customer = User::withoutGlobalScope(ActiveScope::class)->where('id', $clientContact->user_id)->first();
            $user->status = $customer->status;
            $user->update();
        }

        $client_note = new ClientNote();
        $note = trim_editor($request->note);

        if ($note != '') {
            $client_note->title = 'Note';
            $client_note->client_id = $client_id;
            $client_note->details = $note;
            $client_note->save();
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $customer = $user->clientDetails;
            $customer->updateCustomFieldData($request->custom_fields_data);
        }

        $role = Role::where('name', 'customer')->select('id')->first();
        $user->attachRole($role->id);

        $user->assignUserRolePermission($role->id);

        // Log search
        $this->logSearchEntry($user->id, $user->name, 'customers.show', 'customer');

        if (!is_null($user->email)) {
            $this->logSearchEntry($user->id, $user->email, 'customers.show', 'customer');
        }

        if (!is_null($user->clientDetails->company_name)) {
            $this->logSearchEntry($user->id, $user->clientDetails->company_name, 'customers.show', 'customer');
        }

        if ($request->has('enquiry')) {
            $enquiry = Enquiry::findOrFail($request->enquiry);
            $enquiry->client_id = $user->id;
            $enquiry->save();
        }

        DB::commit();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('customers.index');
        }

        if($request->has('is_client_contact')){
            $redirectUrl = route('customers.show', $request->is_client_contact) . '?tab=contacts';
        }

        if ($request->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('team chat.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        if ($request->has('ajax_create')) {
            $sites = Site::all();
            $teams = User::allClients();
            $options = BaseModel::options($sites, null, 'project_name');

            $teamData = '';

            foreach ($teams as $team) {
                $selected = ($team->id == $user->id) ? 'selected' : '';

                $teamData .= '<option ' . $selected . ' data-content="';

                $teamData .= '<div class=\'media align-items-center mw-250\'>';

                $teamData .= '<div class=\'position-relative\'><img src=' . $team->image_url . ' class=\'mr-2 taskEmployeeImg rounded-circle\'></div>';
                $teamData .= '<div class=\'media-body\'>';
                $teamData .= '<h5 class=\'mb-0 f-13\'>' . $team->name . '</h5>';
                $teamData .= '<p class=\'my-0 f-11 text-dark-grey\'>' . $team->email . '</p>';

                $teamData .= (!is_null($team->clientDetails->company_name)) ? '<p class=\'my-0 f-11 text-dark-grey\'>' . $team->clientDetails->company_name . '</p>' : '';
                $teamData .= '</div>';
                $teamData .= '</div>"';

                $teamData .= 'value="' . $team->id . '"> ' . $team->name . '';

                $teamData .= '</option>';
            }

            return Reply::successWithData(__('team chat.recordSaved'), ['teamData' => $teamData, 'site' => $options, 'redirectUrl' => $redirectUrl]);
        }

        return Reply::successWithData(__('team chat.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->customer = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($id);
        $this->emailCountInCompanies = User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where('email', $this->customer->email)
            ->whereNotNull('email')
            ->count();

        $this->editPermission = user()->permission('edit_clients');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->customer->clientDetails->added_by == user()->id) || ($this->editPermission == 'both' && $this->customer->clientDetails->added_by == user()->id)));

        $this->countries = countries();
        $this->categories = ClientCategory::all();

        if ($this->editPermission == 'all') {
            $this->cleaners = User::allEmployees();
        }

        $this->pageTitle = __('app.update') . ' ' . __('app.customer');
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();

        if (!is_null($this->customer->clientDetails)) {
            $this->clientDetail = $this->customer->clientDetails->withCustomFields();

            $getCustomFieldGroupsWithFields = $this->clientDetail->getCustomFieldGroupsWithFields();

            if ($getCustomFieldGroupsWithFields) {
                $this->fields = $getCustomFieldGroupsWithFields->fields;
            }
        }

        $this->subcategories = ClientSubCategory::all();

        $this->view = 'customers.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('customers.create', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $data = $request->all();

        // if(isWorksuiteSaas()) {
        //     if($user->email != $request->email) {
        //         if(is_null($request->password)){
        //             $userAuth = UserAuth::createUserAuthCredentials($request->email, $request->password);
        //             $data['user_auth_id'] = $userAuth->id;
        //         }
        //     }
        //     else {
        //         unset($data['email']);
        //     }
        // }

        // unset($data['password']);
        unset($data['country']);
        unset($data['is_client_contact']);

        if ($request->status != $user->status) {
            $clientIds = ClientContact::where('user_id', $user->id)->pluck('client_id');
            User::withoutGlobalScope(ActiveScope::class)->whereIn('id', $clientIds)->update(['status' => $request->status]);
        }

        $data['country_id'] = $request->country;

        if ($request->has('sendMail')) {
            $user->email_notifications = $request->sendMail == 'yes' ? 1 : 0;
        }

        if ($request->has('telegram_user_id')) {
            $data['telegram_user_id'] = $request->telegram_user_id;
        }


        if ($request->image_delete == 'yes') {
            Files::deleteFile($user->image, 'avatar');
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {

            Files::deleteFile($user->image, 'avatar');
            $data['image'] = Files::uploadLocalOrS3($request->image, 'avatar', 300);
        }

        if($request->has('is_client_contact')){
            $clientContact = ClientContact::findOrFail($request->client_contact_id);
            $clientContact->user_id = $request->is_client_contact;
            $clientContact->contact_name = $request->name;
            $clientContact->phone = $request->mobile;
            $clientContact->email = $request->email;
            $clientContact->title = $request->title;
            $clientContact->address = $request->address;
            $clientContact->last_updated_by = user()->id;
            $clientContact->save();
        }

        $user->update($data);

        if ($user->clientDetails) {
            $data['category_id'] = $request->category_id;
            $data['sub_category_id'] = $request->sub_category_id;
            $data['note'] = trim_editor($request->note);
            $data['locale'] = $request->locale;
            $fields = $request->only($user->clientDetails->getFillable());

            if ($request->has('company_logo_delete') && $request->company_logo_delete == 'yes') {
                Files::deleteFile($user->clientDetails->company_logo, 'customer-logo');
                $fields['company_logo'] = null;
            }

            if ($request->hasFile('company_logo')) {
                Files::deleteFile($user->clientDetails->company_logo, 'customer-logo');
                $fields['company_logo'] = Files::uploadLocalOrS3($request->company_logo, 'customer-logo', 300);
            }

            $user->clientDetails->fill($fields);
            $user->clientDetails->save();

        }
        else {
            $user->clientDetails()->create($data);
        }

        // To add custom fields data
        if ($request->custom_fields_data) {
            $user->clientDetails->updateCustomFieldData($request->custom_fields_data);
        }

        $this->createEmployeeActivity(auth()->user()->id, 'customer-updated', $id, 'customer');


        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('customers.index');
        }

        if($request->has('is_client_contact')){
            $redirectUrl = route('customers.show', $request->is_client_contact) . '?tab=contacts';
        }

        return Reply::successWithData(__('team chat.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->customer = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($id);
        $this->deletePermission = user()->permission('delete_clients');

        abort_403(
            !($this->deletePermission == 'all'
                || ($this->deletePermission == 'added' && $this->customer->clientDetails->added_by == user()->id)
                || ($this->deletePermission == 'both' && $this->customer->clientDetails->added_by == user()->id)
            )
        );

        $this->deleteClient($this->customer);

        return Reply::success(__('team chat.deleteSuccess'));
    }

    private function deleteClient(User $user)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $user->id)->where('module_type', 'customer')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }


        Notification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('data', 'like', '{"id":' . $user->id . ',%');
                $q->orWhere('data', 'like', '%,"name":' . $user->name . ',%');
                $q->orWhere('data', 'like', '%,"user_one":' . $user->id . ',%');
                $q->orWhere('data', 'like', '%,"client_id":' . $user->id . '%');
            })->delete();

        $user->delete();

        Enquiry::where('client_id', $user->id)->update(['client_id' => null]);
        $this->createEmployeeActivity(auth()->user()->id, 'customer-deleted');

        session()->forget('company');

        return Reply::success(__('team chat.deleteSuccess'));

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('team chat.deleteSuccess'));
        case 'change-status':
            $this->changeStatus($request);

            return Reply::success(__('team chat.updateSuccess'));
        default:
            return Reply::error(__('team chat.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_clients') !== 'all');
        $users = User::withoutGlobalScope(ActiveScope::class)->whereIn('id', explode(',', $request->row_ids))->get();
        $users->each(function ($user) {
            $this->deleteClient($user);
        });

        return true;
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_clients') !== 'all');
        User::withoutGlobalScope(ActiveScope::class)
            ->whereIn('id', explode(',', $request->row_ids))
            ->update(['status' => $request->status]);

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->customer = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $this->clientLanguage = LanguageSetting::where('language_code', $this->customer->locale)->first();
        $this->viewPermission = user()->permission('view_clients');
        $this->viewDocumentPermission = user()->permission('view_client_document');

        if (!$this->customer->hasRole('customer')) {
            abort(404);
        }

        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->customer->clientDetails->added_by == user()->id)
            || ($this->viewPermission == 'both' && $this->customer->clientDetails->added_by == user()->id)));

        $this->pageTitle = $this->customer->name;

        $this->clientStats = $this->clientStats($id);
        $this->projectChart = $this->projectChartData($id);
        $this->invoiceChart = $this->invoiceChartData($id);

        $this->earningTotal = Payment::leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('sites', 'sites.id', '=', 'payments.project_id')
            ->where(function ($q) use ($id) {
                $q->where('invoices.client_id', $id);
                $q->orWhere('sites.client_id', $id);
            })->sum('amount');

        $this->view = 'customers.ajax.profile';

        $tab = request('tab');

        switch ($tab) {
        case 'sites':
            return $this->sites();
        case 'invoices':
            return $this->invoices();
        case 'payments':
            return $this->payments();
        case 'quotes':
            return $this->quotes();
        case 'creditnotes':
            return $this->creditnotes();
        case 'contacts':
            return $this->contacts();
        case 'orders':
            return $this->orders();
        case 'documents':
            abort_403(!($this->viewDocumentPermission == 'all'
                || ($this->viewDocumentPermission == 'added' && $this->customer->clientDetails->added_by == user()->id)
                || ($this->viewDocumentPermission == 'owned' && $this->customer->clientDetails->user_id == user()->id)
                || ($this->viewDocumentPermission == 'both' && ($this->customer->clientDetails->added_by == user()->id || $this->customer->clientDetails->user_id == user()->id))));

            $this->view = 'customers.ajax.documents';
            break;
        case 'notes':
            return $this->notes();
        case 'tickets':
            return $this->tickets();
        case 'gdpr':
            $this->customer = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
            $this->consents = PurposeConsent::with(['user' => function ($query) use ($id) {
                $query->where('client_id', $id)
                    ->orderByDesc('created_at');
            }])->get();

            return $this->gdpr();
        default:
            $this->clientDetail = ClientDetails::where('user_id', '=', $this->customer->id)->first();

            if (!is_null($this->clientDetail)) {
                $this->clientDetail = $this->clientDetail->withCustomFields();

                $getCustomFieldGroupsWithFields = $this->clientDetail->getCustomFieldGroupsWithFields();

                if ($getCustomFieldGroupsWithFields) {
                    $this->fields = $getCustomFieldGroupsWithFields->fields;
                }
            }

            $this->view = 'customers.ajax.profile';
            break;
        }

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('customers.show', $this->data);

    }

    public function clientStats($id)
    {
        return DB::table('users')
            ->select(
                DB::raw('(select count(sites.id) from `sites` WHERE sites.client_id = ' . $id . ' and deleted_at IS NULL) as totalProjects'),
                DB::raw('(select count(invoices.id) from `invoices` left join sites on sites.id=invoices.project_id WHERE invoices.status != "paid" and invoices.status != "canceled" and (sites.client_id = ' . $id . ' or invoices.client_id = ' . $id . ')) as totalUnpaidInvoices'),
                DB::raw('(select sum(payments.amount) from `payments` left join sites on sites.id=payments.project_id WHERE payments.status = "complete" and sites.client_id = ' . $id . ') as projectPayments'),
                DB::raw('(select sum(payments.amount) from `payments` inner join invoices on invoices.id=payments.invoice_id  WHERE payments.status = "complete" and invoices.client_id = ' . $id . ') as invoicePayments'),
                DB::raw('(select count(service agreements.id) from `service agreements` WHERE service agreements.client_id = ' . $id . ') as totalContracts')
            )
            ->first();
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function projectChartData($id)
    {
        // Fetch active site status settings
        $statusSettings = ProjectStatusSetting::where('status', 'active')->get();

        // Initialize data array
        $data = [
            'labels' => $statusSettings->pluck('status_name')->toArray(),
            'colors' => $statusSettings->pluck('color')->toArray(),
            'values' => []
        ];

        // Construct the query to count sites for each status
        $query = Site::selectRaw('COUNT(*) as count, status')
            ->where('client_id', $id)
            ->whereIn('status', $data['labels'])
            ->groupBy('status');

        // Execute the query and fetch counts for each status
        $statusCounts = $query->pluck('count', 'status');

        // Populate the values array with counts for each status
        foreach ($data['labels'] as $label) {
            $data['values'][] = $statusCounts[$label] ?? 0;
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function invoiceChartData($id)
    {
        // Define labels, translations, and colors
        $labels = ['paid', 'unpaid', 'partial', 'canceled', 'draft'];
        $translations = [__('app.paid'), __('app.unpaid'), __('app.partial'), __('app.canceled'), __('app.draft')];
        $colors = ['#2CB100', '#FCBD01', '#1d82f5', '#D30000', '#616e80'];

        // Construct the query to count invoices for each status
        $query = Invoice::selectRaw('COUNT(*) as count, status')
            ->where('client_id', $id)
            ->whereIn('status', $labels)
            ->groupBy('status');

        // Execute the query and fetch counts for each status
        $statusCounts = $query->pluck('count', 'status');

        // Initialize data array
        $data = [
            'labels' => $translations,
            'colors' => $colors,
            'values' => []
        ];

        // Populate the values array with counts for each status
        foreach ($labels as $label) {
            $data['values'][] = $statusCounts[$label] ?? 0;
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function projectList($id)
    {
        if ($id != 0) {
            $sites = Site::where('client_id', $id)->get();
            $options = BaseModel::options($sites, null, 'project_name');

        }
        else {
            $options = '<option value="">--</option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxDetails($id)
    {
        if ($id != 0) {
            $customer = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails', 'country')->find($id);

        }
        else {
            $customer = null;
        }

        $clientProjects = Site::where('client_id', $id)->get();

        $options = '<option value="">--</option>';

        foreach ($clientProjects as $site) {

            $options .= '<option value="' . $site->id . '"> ' . $site->project_name . ' </option>';
        }

        $data = $customer ?: null;

        return Reply::dataOnly(['status' => 'success', 'data' => $data, 'site' => $options]);
    }

    public function sites()
    {

        $viewPermission = user()->permission('view_projects');

        abort_403(!($viewPermission == 'all' || $viewPermission == 'added'));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.sites';

        $dataTable = new ProjectsDataTable();

        return $dataTable->render('customers.show', $this->data);

    }

    public function invoices()
    {
        $dataTable = new InvoicesDataTable();
        $viewPermission = user()->permission('view_invoices');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $tab = request('tab');

        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.invoices';

        return $dataTable->render('customers.show', $this->data);
    }

    public function payments()
    {
        $dataTable = new PaymentsDataTable();
        $viewPermission = user()->permission('view_payments');

        abort_403(!($viewPermission == 'all' || $viewPermission == 'added'));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.payments';

        return $dataTable->render('customers.show', $this->data);

    }

    public function quotes()
    {
        $dataTable = new EstimatesDataTable();
        $viewPermission = user()->permission('view_estimates');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'customers.ajax.quotes';

        return $dataTable->render('customers.show', $this->data);
    }

    public function creditnotes()
    {
        $dataTable = new CreditNotesDataTable();
        $viewPermission = user()->permission('view_invoices');

        abort_403($viewPermission == 'none');
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'customers.ajax.credit_notes';

        return $dataTable->render('customers.show', $this->data);
    }

    public function contacts()
    {
        $dataTable = new ClientContactsDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.contacts';

        return $dataTable->render('customers.show', $this->data);
    }

    public function notes()
    {
        $dataTable = new ClientNotesDataTable();
        $viewPermission = user()->permission('view_client_note');

        abort_403(($viewPermission == 'none'));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'customers.ajax.notes';

        return $dataTable->render('customers.show', $this->data);
    }

    public function tickets()
    {
        $dataTable = new TicketDataTable();
        $viewPermission = user()->permission('view_clients');

        abort_403(!($viewPermission == 'all' || $viewPermission == 'added' || $viewPermission == 'both'));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.tickets';

        return $dataTable->render('customers.show', $this->data);
    }

    public function gdpr()
    {
        $dataTable = new ClientGDPRDataTable();
        $tab = request('tab');
        $this->activeTab = $tab ?: 'gdpr';

        $this->view = 'customers.ajax.gdpr';

        return $dataTable->render('customers.show', $this->data);
    }

    public function consent(Request $request)
    {
        $clientId = $request->clientId;
        $this->consentId = $request->consentId;
        $this->clientId = $clientId;

        $this->consent = PurposeConsent::with(['user' => function ($query) use ($request) {
            $query->where('client_id', $request->clientId)
                ->orderByDesc('created_at');
        }])
            ->where('id', $request->consentId)
            ->first();

        return view('customers.gdpr.consent-form', $this->data);
    }

    public function saveClientConsent(SaveConsentUserDataRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $consent = PurposeConsent::findOrFail($request->consent_id);

        if ($request->consent_description && $request->consent_description != '') {
            $consent->description = trim_editor($request->consent_description);
            $consent->save();
        }

        // Saving Consent Data
        $newConsentLead = new PurposeConsentUser();
        $newConsentLead->client_id = $user->id;
        $newConsentLead->purpose_consent_id = $consent->id;
        $newConsentLead->status = trim($request->status);
        $newConsentLead->ip = $request->ip();
        $newConsentLead->updated_by_id = $this->user->id;
        $newConsentLead->additional_description = $request->additional_description;
        $newConsentLead->save();

        return $request->status == 'agree' ? Reply::success(__('team chat.consentOptIn')) : Reply::success(__('team chat.consentOptOut'));
    }

    public function approve($id)
    {
        abort_403(!in_array('admin', user_roles()));

        User::where('id', $id)->update(
            ['admin_approval' => 1]
        );

        $userSession = new AppSettingController();
        $userSession->deleteSessions([$id]);

        $password = session('auth_pass');
        $customer = User::where('id', $id)->first();

        event(new NewUserEvent($customer, $password, true));

        return Reply::success(__('team chat.updateSuccess'));
    }

    public function importClient()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.customer');

        $addPermission = user()->permission('add_clients');
        abort_403(!in_array($addPermission, ['all', 'added', 'both']));

        $this->view = 'customers.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('customers.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ClientImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('team chat.abortAction'));
        }

        $view = view('customers.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('team chat.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ClientImport::class, ImportClientJob::class);

        return Reply::successWithData(__('team chat.importProcessStart'), ['batch' => $batch]);
    }

    public function financeCount(Request $request)
    {
        $id = $request->id;

        $counts = User::withCount('sites', 'invoices', 'quotes')->withoutGlobalScope(ActiveScope::class)->find($id);

        $payments = Payment::leftJoin('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('sites', 'sites.id', '=', 'payments.project_id')
            ->leftJoin('orders', 'orders.id', '=', 'payments.order_id')
            ->where(function ($query) use ($id) {
                $query->where('sites.client_id', $id)
                    ->orWhere('invoices.client_id', $id)
                    ->orWhere('orders.client_id', $id);
            })->count();

        $projectName = $counts->projects_count > 1 ? __('app.menu.sites') : __('app.site');
        $invoiceName = $counts->invoices_count > 1 ? __('app.menu.invoices') : __('app.invoice');
        $estimateName = $counts->estimates_count > 1 ? __('app.menu.quotes') : __('app.quote');
        $paymentName = $payments > 1 ? __('app.menu.payments') : __('app.payment');

        $deleteClient = (__('team chat.clientFinanceCount', ['projectCount' => $counts->projects_count, 'invoiceCount' => $counts->invoices_count, 'estimateCount' => $counts->estimates_count, 'paymentCount' => $payments, 'site' => $projectName, 'invoice' => $invoiceName, 'quote' => $estimateName, 'payment' => $paymentName]));

        return Reply::dataOnly(['status' => 'success', 'deleteClient' => $deleteClient]);
    }

    public function clientDetails(Request $request)
    {
        $teamData = '';

        if ($request->id == 0) {
            $customers = User::allClients();

            foreach ($customers as $customer) {

                $teamData .= '<option data-content="';

                $teamData .= '<div class=\'media align-items-center mw-250\'>';

                $teamData .= '<div class=\'position-relative\'><img src=' . $customer->image_url . ' class=\'mr-2 taskEmployeeImg rounded-circle\'></div>';
                $teamData .= '<div class=\'media-body\'>';
                $teamData .= '<h5 class=\'mb-0 f-13\'>' . $customer->name . '</h5>';
                $teamData .= '<p class=\'my-0 f-11 text-dark-grey\'>' . $customer->email . '</p>';

                $teamData .= (!is_null($customer->clientDetails->company_name)) ? '<p class=\'my-0 f-11 text-dark-grey\'>' . $customer->clientDetails->company_name . '</p>' : '';
                $teamData .= '</div>';
                $teamData .= '</div>"';

                $teamData .= 'value="' . $customer->id . '"> ' . $customer->name . '';

                $teamData .= '</option>';

            }
        }
        else {

            $site = Site::with('customer')->findOrFail($request->id);

            if ($site->customer != null) {

                $teamData .= '<option data-content="';

                $teamData .= '<div class=\'media align-items-center mw-250\'>';

                $teamData .= '<div class=\'position-relative\'><img src=' . $site->customer->image_url . ' class=\'mr-2 taskEmployeeImg rounded-circle\'></div>';
                $teamData .= '<div class=\'media-body\'>';
                $teamData .= '<h5 class=\'mb-0 f-13\'>' . $site->customer->name . '</h5>';
                $teamData .= '<p class=\'my-0 f-11 text-dark-grey\'>' . $site->customer->email . '</p>';

                $teamData .= (!is_null($site->customer->company->company_name)) ? '<p class=\'my-0 f-11 text-dark-grey\'>' . $site->customer->company->company_name . '</p>' : '';
                $teamData .= '</div>';
                $teamData .= '</div>"';

                $teamData .= 'value="' . $site->customer->id . '"> ' . $site->customer->name . '';

                $teamData .= '</option>';
            }

        }

        return Reply::dataOnly(['teamData' => $teamData]);
    }

    public function orders()
    {
        $dataTable = new OrdersDataTable();
        $viewPermission = user()->permission('view_order');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $tab = request('tab');

        $this->activeTab = $tab ?: 'profile';

        $this->view = 'customers.ajax.orders';

        return $dataTable->render('customers.show', $this->data);
    }

}
