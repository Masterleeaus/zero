<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Insights;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Models\Money\Quote;
use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use App\Models\UserSupport;
use App\Models\Work\Timelog;
use App\Models\Work\Attendance;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InsightsController extends CoreController
{
    public function overview(Request $request): View
    {
        $companyId = $request->user()?->company_id;

        $enquiries = Enquiry::query()->where('company_id', $companyId)->count();
        $customers = Customer::query()->where('company_id', $companyId)->count();
        $sites = Site::query()->where('company_id', $companyId)->where('status', 'active')->count();

        $jobStatus = ServiceJob::query()
            ->where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $quoteStatus = Quote::query()
            ->where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $invoiceStatus = Invoice::query()
            ->where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $overdueInvoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'void'])
            ->whereDate('due_date', '<', now())
            ->count();

        $outstandingBalance = (float) Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'void'])
            ->sum('balance');

        $paymentsTotal = (float) Payment::query()
            ->where('company_id', $companyId)
            ->sum('amount');

        $quoteToJobCount = ServiceJob::query()
            ->where('company_id', $companyId)
            ->whereNotNull('quote_id')
            ->count();

        $quoteToInvoiceCount = Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotNull('quote_id')
            ->count();

        $supportOpen = UserSupport::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['open'])
            ->count();

        $supportWaitingTeam = UserSupport::query()
            ->where('company_id', $companyId)
            ->where('status', 'waiting_on_team')
            ->count();

        $supportWaitingUser = UserSupport::query()
            ->where('company_id', $companyId)
            ->where('status', 'waiting_on_user')
            ->count();

        $supportResolved = UserSupport::query()
            ->where('company_id', $companyId)
            ->where('status', 'resolved')
            ->count();

        $timelogMinutes = Timelog::query()
            ->where('company_id', $companyId)
            ->sum(DB::raw('COALESCE(duration_minutes, 0)'));

        $attendanceOpen = Attendance::query()
            ->where('company_id', $companyId)
            ->where('status', 'checked_in')
            ->count();

        $attendanceSummary = Attendance::statusSummary($companyId);

        $shiftsScheduled = Shift::query()->where('company_id', $companyId)->count();
        $shiftsUnassigned = Shift::query()->where('company_id', $companyId)->unassigned()->count();
        $lateAttendance = $attendanceSummary['late'] ?? 0;

        $dueAgreements = ServiceAgreement::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->whereDate('next_run_at', '<=', now())
            ->count();

        $agreementsActive = ServiceAgreement::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        $upcomingJobs = ServiceJob::query()
            ->where('company_id', $companyId)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->count();

        $unassignedJobs = ServiceJob::query()
            ->where('company_id', $companyId)
            ->whereNull('assigned_user_id')
            ->count();

        $recentCustomers = Customer::query()
            ->where('company_id', $companyId)
            ->latest()
            ->limit(5)
            ->get();

        $unreadNotifications = auth()->user()
            ?->notifications()
            ->whereNull('read_at')
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->count() ?? 0;

        return view('default.panel.user.insights.overview', [
            'enquiryCount' => $enquiries,
            'customerCount'=> $customers,
            'recentCustomers' => $recentCustomers,
            'activeSites'  => $sites,
            'jobStatus'    => $jobStatus,
            'upcomingJobs' => $upcomingJobs,
            'unassignedJobs' => $unassignedJobs,
            'quoteStatus'  => $quoteStatus,
            'invoiceStatus'=> $invoiceStatus,
            'overdueInvoices' => $overdueInvoices,
            'outstandingBalance' => $outstandingBalance,
            'paymentsTotal' => $paymentsTotal,
            'quoteToJobCount' => $quoteToJobCount,
            'quoteToInvoiceCount' => $quoteToInvoiceCount,
            'companyId' => $companyId,
            'supportOpen' => $supportOpen,
            'supportWaitingTeam' => $supportWaitingTeam,
            'supportWaitingUser' => $supportWaitingUser,
            'supportResolved' => $supportResolved,
            'timelogHours' => round($timelogMinutes / 60, 1),
            'attendanceOpen' => $attendanceOpen,
            'attendanceSummary' => $attendanceSummary,
            'agreementsActive' => $agreementsActive,
            'dueAgreements' => $dueAgreements,
            'shiftsScheduled' => $shiftsScheduled,
            'shiftsUnassigned' => $shiftsUnassigned,
            'lateAttendance' => $lateAttendance,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function reports(): View
    {
        return $this->placeholder(__('Reports'), __('Reports scoped to the current company.'));
    }
}
