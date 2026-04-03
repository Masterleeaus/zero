<?php

namespace Modules\Kontrak\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Ticket;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Currency;
use App\Models\ContractType;
use Illuminate\Http\Request;
use App\Models\ContractTemplate;
use Modules\Units\Entities\Unit;
use App\Http\Controllers\AccountBaseController;
use Modules\Kontrak\DataTables\RecurringKontrakDatatable;
use Modules\Kontrak\Entities\RecurringKontrak;
use Modules\Engineerings\DataTables\RecurringSchedulesDataTable;
use Modules\Kontrak\Http\Requests\StoreKontrak;

class RecurringKontrakController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'kontrak::modules.recContract';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('kontrak', $this->user->modules));

            return $next($request);
        });
    }

    public function index(RecurringKontrakDatatable $dataTable)
    {
        $viewPermission = user()->permission('view_kontrak');
        abort_403(!in_array($viewPermission, ['all']));
        return $dataTable->render('kontrak::recurring-kontrak.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_kontrak');
        abort_403(!in_array($this->addPermission, ['all']));
        $this->pageTitle  = __('app.add') . ' ' . __('kontrak::modules.recContract');
        $this->contractId = request('id');
        $this->contract   = null;
        if ($this->contractId != '') {
            $this->contractTemplate = Contract::findOrFail($this->contractId);
        }
        $this->clients        = User::allClients(null, ($this->addPermission == 'all' ? 'all' : null));
        $this->contractTypes  = ContractType::all();
        $this->currencies     = Currency::all();
        $this->projects       = Project::all();
        $this->units          = Unit::all();
        $this->lastContract   = Contract::lastContractNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->zero           = '';
        if (strlen($this->lastContract) < $this->invoiceSetting->contract_digit) {
            $condition = $this->invoiceSetting->contract_digit - strlen($this->lastContract);
            for ($i = 0; $i < $condition; $i++) {
                $this->zero = '0' . $this->zero;
            }
        }
        if (is_null($this->contractId)) {
            $this->contractTemplate = request('template') ? ContractTemplate::findOrFail(request('template')) : null;
        }
        $contract = new Contract();
        if (!empty($contract->getCustomFieldGroupsWithFields())) {
            $this->fields = $contract->getCustomFieldGroupsWithFields()->fields;
        }
        $this->view = 'kontrak::recurring-kontrak.ajax.create';
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        return view('kontrak::recurring-kontrak.create', $this->data);
    }

    public function store(StoreKontrak $request)
    {
        $contract                      = new Contract();
        $contract->client_id           = $request->client_id;
        $contract->project_id          = $request->project_id;
        $contract->subject             = $request->subject;
        $contract->amount              = $request->amount;
        $contract->currency_id         = $request->currency_id;
        $contract->original_amount     = $request->amount;
        $contract->contract_name       = $request->contract_name;
        $contract->alternate_address   = $request->alternate_address;
        $contract->contract_note       = $request->note;
        $contract->cell                = $request->cell;
        $contract->office              = $request->office;
        $contract->city                = $request->city;
        $contract->state               = $request->state;
        $contract->country             = $request->country;
        $contract->postal_code         = $request->postal_code;
        $contract->contract_type_id    = $request->contract_type;
        $contract->contract_number     = $request->contract_number;
        $contract->start_date          = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
        $contract->original_start_date = Carbon::createFromFormat($this->company->date_format, $request->start_date)->format('Y-m-d');
        $contract->end_date            = $request->end_date == null ? $request->end_date : Carbon::createFromFormat($this->company->date_format, $request->end_date)->format('Y-m-d');
        $contract->original_end_date   = $request->end_date == null ? $request->end_date : Carbon::createFromFormat($this->company->date_format, $request->end_date)->format('Y-m-d');
        $contract->description         = $request->description;
        $contract->contract_detail     = $request->contract_detail;
        $contract->issue_date          = !is_null($request->issue_date) ? Carbon::createFromFormat($this->company->date_format, $request->issue_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $contract->save();

        $contract_detail                      = new RecurringKontrak();
        $contract_detail->contract_id         = $contract->id;
        $contract_detail->unit_id             = $request->unit_id;
        $contract_detail->rate                = $request->rate;
        $contract_detail->issue_date          = !is_null($request->issue_date) ? Carbon::createFromFormat($this->company->date_format, $request->issue_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $contract_detail->rotation            = $request->rotation;
        $contract_detail->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
        $contract_detail->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
        $contract_detail->immediate_schedule  = ($request->immediate_schedule) ? 1 : 0;
        $contract_detail->created_by          = user()->id;
        $contract_detail->save();

        if ($request->immediate_schedule) {
            $invoice                      = new Invoice();
            $invoice->project_id          = $contract->project_id;
            $invoice->client_id           = $contract->client_id;
            $invoice->issue_date          = Carbon::now()->format('Y-m-d');
            $invoice->sub_total           = $contract_detail->rate;
            $invoice->total               = $contract_detail->rate;
            $invoice->due_amount          = $contract->amount;
            $invoice->currency_id         = $contract->currency_id;
            $invoice->default_currency_id = company()->currency_id;
            $invoice->recurring           = 'no';
            $invoice->invoice_number      = Invoice::lastInvoiceNumber() + 1;
            $invoice->company_address_id  = company()->id;
            $invoice->save();
        }

        return Reply::redirect(route('kontrak.index'), __('kontrak::messages.addContract'));
    }

    public function show($id)
    {
        $this->schedule = RecurringKontrak::with('recurrings')->findOrFail($id);
        $this->settings = $this->company;

        $tab = request('tab');
        switch ($tab) {
            case 'schedules':
                return $this->schedules($id);
            default:
                $this->view = 'kontrak::recurring-kontrak.ajax.overview';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'overview';
        return view('kontrak::recurring-kontrak.show', $this->data);
    }

    public function edit($id)
    {
        $this->schedule       = RecurringKontrak::with('recurrings')->findOrFail($id);
        $this->editPermission = user()->permission('edit_kontrak');
        $this->contractTypes  = ContractType::all();
        $this->units          = Unit::all();

        abort_403(!($this->editPermission == 'all'));
        return view('kontrak::recurring-kontrak.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $recurringKontrak = RecurringKontrak::findOrFail($id);

        if ($request->schedule_count == 0) {
            $recurringKontrak->unit_id = $request->unit_id;
            $recurringKontrak->rate    = $request->rate;

            $recurringKontrak->issue_date          = !is_null($request->issue_date) ? Carbon::createFromFormat($this->company->date_format, $request->issue_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $recurringKontrak->rotation            = $request->rotation;
            $recurringKontrak->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
            $recurringKontrak->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
            $recurringKontrak->created_by          = user()->id;

            if ($request->rotation == 'weekly' || $request->rotation == 'bi-weekly') {
                $recurringKontrak->day_of_week = $request->day_of_week;
            } elseif ($request->rotation == 'monthly' || $request->rotation == 'quarterly' || $request->rotation == 'half-yearly' || $request->rotation == 'annually') {
                $recurringKontrak->day_of_month = $request->day_of_month;
            }
            if (request()->has('status')) {
                $recurringKontrak->status = $request->status;
            }

            $recurringKontrak->save();
        } else {

            if (request()->has('status')) {
                $recurringKontrak->status = $request->status;
            }
            $recurringKontrak->save();
        }

        return Reply::redirect(route('kontrak.index'), __('kontrak::messages.updateContract'));
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_kontrak');

        $recurringSchedule = RecurringKontrak::findOrFail($id);
        abort_403(!($this->deletePermission == 'all'));

        RecurringKontrak::destroy($id);
        return Reply::success(__('kontrak::messages.deleteContract'));
    }

    public function changeStatus(Request $request)
    {
        $scheduleId = $request->scheduleId;
        $status     = $request->status;
        $schedule   = RecurringKontrak::findOrFail($scheduleId);

        if ($schedule) {
            $schedule->status = $status;
            $schedule->save();
        }

        return Reply::success(__('kontrak::messages.updateContract'));
    }

    public function recurringSchedules(RecurringSchedulesDataTable $dataTable, $id)
    {
        $this->schedule = RecurringKontrak::findOrFail($id);

        return $dataTable->render('kontrak.recurring-schedules', $this->data);
    }

    public function schedules($recurringID)
    {
        $dataTable         = new RecurringSchedulesDataTable;
        $this->recurringID = $recurringID;
        $tab               = request('tab');
        $this->activeTab   = $tab ?: 'overview';
        $this->view        = 'kontrak::recurring-kontrak.ajax.schedules';
        return $dataTable->render('kontrak::recurring-kontrak.show', $this->data);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('kontrak::messages.deleteContract'));
            default:
                return Reply::error(__('kontrak::messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_kontrak') != 'all');

        $items = explode(',', $request->row_ids);

        foreach ($items as $id) {
            RecurringKontrak::destroy($id);
        }
    }
}
