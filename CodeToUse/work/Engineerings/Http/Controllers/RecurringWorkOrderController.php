<?php

namespace Modules\Engineerings\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Helper\Reply;
use App\Models\Ticket;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Modules\Engineerings\Entities\WorkRequest;
use App\Http\Controllers\AccountBaseController;
use Modules\Engineerings\DataTables\RecurringWorkDataTable;
use Modules\Engineerings\DataTables\RecurringSchedulesDataTable;
use Modules\Engineerings\Entities\RecurringWorkOrder;
use Modules\Engineerings\Entities\WorkOrder;
use Modules\Engineerings\Http\Requests\RecurringWorkOrderRequest;
use Modules\Engineerings\Http\Requests\WrRequest;
use Modules\Units\Entities\Unit;

class RecurringWorkOrderController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'engineerings::modules.recWorkOrders';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('engineerings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(RecurringWorkDataTable $dataTable)
    {
        $permissions = user()->permission('view_eng');
        abort_403(!in_array($permissions, ['all']));

        return $dataTable->render('engineerings::recurring-workorder.index', $this->data);
    }

    public function create()
    {
        $this->permissions = user()->permission('add_eng');
        abort_403(!in_array($this->permissions, ['all']));

        $this->pageTitle = __('engineerings::app.recWorkOrder.addRecWO');
        $this->ticket    = Ticket::all();
        $this->invoice   = Invoice::all();
        $this->wr        = WorkRequest::all();
        $this->unit      = Unit::all();
        $this->view      = 'engineerings::recurring-workorder.ajax.create';
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }
        return view('engineerings::recurring-workorder.create', $this->data);
    }

    public function store(RecurringWorkOrderRequest $request)
    {
        $reccuringsWork                   = new RecurringWorkOrder();
        $reccuringsWork->workrequest_id   = $request->workrequest_id;
        $reccuringsWork->category         = $request->category;
        $reccuringsWork->priority         = $request->priority;
        $reccuringsWork->status_wo        = 'incomplete';
        $reccuringsWork->work_description = $request->work_description;
        $reccuringsWork->schedule_start   = $request->schedule_start;
        $reccuringsWork->schedule_finish  = $request->schedule_finish;
        $reccuringsWork->estimate_hours   = $request->estimate_hours;
        $reccuringsWork->estimate_minutes = $request->estimate_minutes;
        $reccuringsWork->unit_id          = $request->unit_id;
        $reccuringsWork->assets_id        = $request->assets_id;
        $reccuringsWork->status           = 'active';

        $reccuringsWork->issue_date          = !is_null($request->issue_date) ? Carbon::createFromFormat($this->company->date_format, $request->issue_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $reccuringsWork->rotation            = $request->rotation;
        $reccuringsWork->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
        $reccuringsWork->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
        $reccuringsWork->immediate_schedule  = $request->immediate_schedule ? 1 : 0;
        $reccuringsWork->created_by          = user()->id;
        $reccuringsWork->save();

        if ($request->immediate_schedule) {
            $this->number = WorkOrder::lastInvoiceNumber() + 1;
            $this->zero   = '';
            if (strlen($this->number) < 4) {
                for ($i = 0; $i < 4 - strlen($this->number); $i++) {
                    $this->zero = '0' . $this->zero;
                }
            }
            $this->nomor                       = 'WO-' . Carbon::now()->format('ym') . '-' . $this->zero . $this->number;
            $workOrder                         = new WorkOrder();
            $workOrder->workorder_recurring_id = $reccuringsWork->id;
            $workOrder->workrequest_id         = $reccuringsWork->workrequest_id;
            $workOrder->category               = $reccuringsWork->category;
            $workOrder->priority               = $reccuringsWork->priority;
            $workOrder->status                 = $reccuringsWork->status_wo;
            $workOrder->work_description       = $reccuringsWork->work_description;
            $workOrder->schedule_start         = $reccuringsWork->schedule_start;
            $workOrder->schedule_finish        = $reccuringsWork->schedule_finish;
            $workOrder->estimate_hours         = $reccuringsWork->estimate_hours;
            $workOrder->estimate_minutes       = $reccuringsWork->estimate_minutes;
            $workOrder->unit_id                = $reccuringsWork->unit_id;
            $workOrder->assets_id              = $reccuringsWork->assets_id;
            $workOrder->nomor_wo               = $this->nomor;
            $workOrder->created_by             = user()->id;
            $workOrder->save();
        }

        return Reply::successWithData(__('engineerings::messages.addRecWO'), ['redirectUrl' => route('recurring-work.index')]);
    }

    public function show($id)
    {
        $permissions = user()->permission('edit_eng');
        abort_403(!in_array($permissions, ['all']));

        $this->pageTitle = __('engineerings::app.recWorkOrder.showRecWO');
        $this->schedule  = RecurringWorkOrder::with('recurrings', 'wr', 'unit', 'assets.type')->findOrFail($id);
        $this->settings  = $this->company;

        $tab = request('tab');
        switch ($tab) {
            case 'schedules':
                return $this->schedules($id);
            default:
                $this->view = 'engineerings::recurring-workorder.ajax.overview';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'overview';
        return view('engineerings::recurring-workorder.show', $this->data);
    }

    public function edit($id)
    {
        $permissions = user()->permission('edit_eng');
        abort_403(!in_array($permissions, ['all']));

        $this->pageTitle = __('engineerings::app.recWorkOrder.editRecWO');
        $this->schedule  = RecurringWorkOrder::with('recurrings')->findOrFail($id);
        $this->ticket    = Ticket::all();
        $this->invoice   = Invoice::all();
        $this->wr        = WorkRequest::all();
        $this->unit      = Unit::all();
        return view('engineerings::recurring-workorder.edit', $this->data);
    }

    public function update(RecurringWorkOrderRequest $request, $id)
    {
        $reccuringsWork = RecurringWorkOrder::findOrFail($id);

        if ($request->schedule_count == 0) {
            $reccuringsWork->workrequest_id   = $request->workrequest_id;
            $reccuringsWork->category         = $request->category;
            $reccuringsWork->priority         = $request->priority;
            $reccuringsWork->status_wo        = 'incomplete';
            $reccuringsWork->work_description = $request->work_description;
            $reccuringsWork->schedule_start   = $request->schedule_start;
            $reccuringsWork->schedule_finish  = $request->schedule_finish;
            $reccuringsWork->estimate_hours   = $request->estimate_hours;
            $reccuringsWork->estimate_minutes = $request->estimate_minutes;
            $reccuringsWork->unit_id          = $request->unit_id;
            $reccuringsWork->assets_id        = $request->assets_id;
            $reccuringsWork->status           = 'active';

            $reccuringsWork->issue_date          = !is_null($request->issue_date) ? Carbon::createFromFormat($this->company->date_format, $request->issue_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $reccuringsWork->rotation            = $request->rotation;
            $reccuringsWork->billing_cycle       = $request->billing_cycle > 0 ? $request->billing_cycle : null;
            $reccuringsWork->unlimited_recurring = $request->billing_cycle < 0 ? 1 : 0;
            $reccuringsWork->created_by          = user()->id;

            if ($request->rotation == 'weekly' || $request->rotation == 'bi-weekly') {
                $reccuringsWork->day_of_week = $request->day_of_week;
            } elseif ($request->rotation == 'monthly' || $request->rotation == 'quarterly' || $request->rotation == 'half-yearly' || $request->rotation == 'annually') {
                $reccuringsWork->day_of_month = $request->day_of_month;
            }
            if (request()->has('status')) {
                $reccuringsWork->status = $request->status;
            }

            $reccuringsWork->save();
        } else {
            if (request()->has('status')) {
                $reccuringsWork->status = $request->status;
            }
            $reccuringsWork->save();
        }

        return Reply::successWithData(__('engineerings::messages.updateRecWO'), ['redirectUrl' => route('recurring-work.index')]);
    }

    public function destroy($id)
    {
        $this->permissions = user()->permission('delete_eng');
        abort_403(!($this->permissions == 'all'));

        $recurringSchedule = RecurringWorkOrder::findOrFail($id);
        RecurringWorkOrder::destroy($id);
        return Reply::success(__('engineerings::messages.deleteRecWO'));
    }

    public function changeStatus(Request $request)
    {
        $scheduleId = $request->scheduleId;
        $status     = $request->status;
        $schedule   = RecurringWorkOrder::findOrFail($scheduleId);

        if ($schedule) {
            $schedule->status = $status;
            $schedule->save();
        }

        return Reply::success(__('engineerings::messages.updateRecWO'));
    }

    public function recurringSchedules(RecurringSchedulesDataTable $dataTable, $id)
    {
        $this->schedule = RecurringWorkOrder::findOrFail($id);
        return $dataTable->render('recurring-work.recurring-schedules', $this->data);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                return Reply::success(__('engineerings::messages.deleteRecWO'));
            default:
                return Reply::error(__('engineerings::messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_eng') != 'all');

        $items = explode(',', $request->row_ids);
        foreach ($items as $id) {
            RecurringWorkOrder::destroy($id);
        }
    }

    public function schedules($recurringID)
    {
        $dataTable         = new RecurringSchedulesDataTable();
        $this->recurringID = $recurringID;
        $tab               = request('tab');
        $this->activeTab   = $tab ?: 'overview';
        $this->view        = 'engineerings::recurring-workorder.ajax.schedules';
        return $dataTable->render('engineerings::recurring-workorder.show', $this->data);
    }
}
