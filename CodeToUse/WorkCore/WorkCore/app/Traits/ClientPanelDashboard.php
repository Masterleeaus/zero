<?php

namespace App\Traits;

use App\Models\ContractSign;
use App\Models\Invoice;
use App\Models\Site;
use App\Models\ProjectMilestone;
use App\Models\ProjectStatusSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helper\UserService;

/**
 *
 */
trait ClientPanelDashboard
{

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function clientPanelDashboard()
    {
        $viewInvoicePermission = user()->permission('view_invoices');

        $id = UserService::getUserId();

        $this->modules = user_modules();
        $this->counts = User::select(
            DB::raw('(select count(sites.id) from `sites` where client_id = ' . $id . ' and deleted_at IS NULL and sites.company_id = ' . company()->id . ') as totalProjects'),
            DB::raw('(select count(tickets.id) from `tickets` where (status="open" or status="pending") and user_id = ' . $id . '  and tickets.company_id = ' . company()->id . ' and deleted_at IS NULL) as totalUnResolvedTickets')
        )
            ->first();

        // Invoices paid
        $this->totalPaidInvoice = Invoice::where(function ($query) {
            $query->where('invoices.status', 'paid');
        })
            ->where('invoices.client_id', $id)
            ->where('invoices.send_status', 1)
            ->where('invoices.credit_note', 0)
            ->select(
                'invoices.id'
            );

        if ($viewInvoicePermission == 'added') {
            $this->totalPaidInvoice = $this->totalPaidInvoice->where('invoices.added_by', $id);
        }

        $this->totalPaidInvoice = $this->totalPaidInvoice->count();


        // Total Pending invoices
        $this->totalUnPaidInvoice = Invoice::where(function ($query) {
            $query->where('invoices.status', 'unpaid')
                ->orWhere('invoices.status', 'partial');
        })
            ->where('invoices.client_id', $id)
            ->where('invoices.send_status', 1)
            ->where('invoices.credit_note', 0)
            ->select(
                'invoices.id'
            );

        if ($viewInvoicePermission == 'added') {
            $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->where('invoices.added_by', $id);
        }

        $this->totalUnPaidInvoice = $this->totalUnPaidInvoice->count();

        $this->totalContractsSigned = ContractSign::whereHas('service agreement', function ($query) use ($id) {
            $query->where('client_id', $id);
        })->count();

        $this->viewMilestonePermission = user()->permission('view_project_milestones');

        $this->pendingMilestone = ProjectMilestone::query();

        if ($this->viewMilestonePermission != 'none') {
            $this->pendingMilestone = ProjectMilestone::with('site', 'currency')
                ->whereHas('site', function ($query) use ($id) {
                    $query->where('client_id', $id);
                })
                ->where('status', 'incomplete')
                ->get();
        }


        $this->statusWiseProject = $this->projectStatusChartData();

        return view('dashboard.customer.index', $this->data);
    }

    public function projectStatusChartData()
    {
        $labels = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['labels'] = ProjectStatusSetting::where('status', 'active')->pluck('status_name');
        $data['colors'] = ProjectStatusSetting::where('status', 'active')->pluck('color');
        $data['values'] = [];

        $id = UserService::getUserId();

        foreach ($labels as $label) {
            $data['values'][] = Site::where('client_id', $id)->where('status', $label)->count();
        }

        return $data;
    }

}
