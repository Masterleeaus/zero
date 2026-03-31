<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Insights;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Models\Work\Leave;
use App\Models\Money\Expense;
use App\Models\Money\Quote;
use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use App\Models\UserSupport;
use App\Models\Work\Timelog;
use App\Models\Work\Attendance;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\Shift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Support\DateQueryHelper;

class InsightsController extends CoreController
{
    private const EXPENSE_REVENUE_MONTH_WINDOW = 6;

    public function overview(Request $request): View
    {
        $companyId = $request->user()?->company_id;

        $stats = Cache::remember("insights_overview_{$companyId}", 300, static function () use ($companyId) {
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

            $leaveTotals = Leave::query()->where('company_id', $companyId)->count();
            $upcomingLeave = Leave::query()->where('company_id', $companyId)->upcoming()->count();
            $leaveShiftConflicts = Leave::conflictsWithShifts($companyId);

            $expenseTotal = Expense::totalForCompany($companyId);
            $expenseByCategory = Expense::totalsByCategory($companyId);

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

            return [
                'enquiries' => $enquiries,
                'customers' => $customers,
                'sites' => $sites,
                'jobStatus' => $jobStatus,
                'quoteStatus' => $quoteStatus,
                'invoiceStatus' => $invoiceStatus,
                'overdueInvoices' => $overdueInvoices,
                'outstandingBalance' => $outstandingBalance,
                'paymentsTotal' => $paymentsTotal,
                'quoteToJobCount' => $quoteToJobCount,
                'quoteToInvoiceCount' => $quoteToInvoiceCount,
                'supportOpen' => $supportOpen,
                'supportWaitingTeam' => $supportWaitingTeam,
                'supportWaitingUser' => $supportWaitingUser,
                'supportResolved' => $supportResolved,
                'timelogMinutes' => $timelogMinutes,
                'attendanceOpen' => $attendanceOpen,
                'attendanceSummary' => $attendanceSummary,
                'shiftsScheduled' => $shiftsScheduled,
                'shiftsUnassigned' => $shiftsUnassigned,
                'lateAttendance' => $lateAttendance,
                'dueAgreements' => $dueAgreements,
                'agreementsActive' => $agreementsActive,
                'leaveTotals' => $leaveTotals,
                'upcomingLeave' => $upcomingLeave,
                'leaveShiftConflicts' => $leaveShiftConflicts,
                'expenseTotal' => $expenseTotal,
                'expenseByCategory' => $expenseByCategory,
                'upcomingJobs' => $upcomingJobs,
                'unassignedJobs' => $unassignedJobs,
                'recentCustomers' => $recentCustomers,
                'unreadNotifications' => $unreadNotifications,
            ];
        });

        return view('default.panel.user.insights.overview', [
            'enquiryCount' => $stats['enquiries'],
            'customerCount'=> $stats['customers'],
            'recentCustomers' => $stats['recentCustomers'],
            'activeSites'  => $stats['sites'],
            'jobStatus'    => $stats['jobStatus'],
            'upcomingJobs' => $stats['upcomingJobs'],
            'unassignedJobs' => $stats['unassignedJobs'],
            'quoteStatus'  => $stats['quoteStatus'],
            'invoiceStatus'=> $stats['invoiceStatus'],
            'overdueInvoices' => $stats['overdueInvoices'],
            'outstandingBalance' => $stats['outstandingBalance'],
            'paymentsTotal' => $stats['paymentsTotal'],
            'quoteToJobCount' => $stats['quoteToJobCount'],
            'quoteToInvoiceCount' => $stats['quoteToInvoiceCount'],
            'companyId' => $companyId,
            'supportOpen' => $stats['supportOpen'],
            'supportWaitingTeam' => $stats['supportWaitingTeam'],
            'supportWaitingUser' => $stats['supportWaitingUser'],
            'supportResolved' => $stats['supportResolved'],
            'timelogHours' => round($stats['timelogMinutes'] / 60, 1),
            'attendanceOpen' => $stats['attendanceOpen'],
            'attendanceSummary' => $stats['attendanceSummary'],
            'agreementsActive' => $stats['agreementsActive'],
            'dueAgreements' => $stats['dueAgreements'],
            'shiftsScheduled' => $stats['shiftsScheduled'],
            'shiftsUnassigned' => $stats['shiftsUnassigned'],
            'lateAttendance' => $stats['lateAttendance'],
            'unreadNotifications' => $stats['unreadNotifications'],
            'leaveTotals' => $stats['leaveTotals'],
            'upcomingLeave' => $stats['upcomingLeave'],
            'leaveShiftConflicts' => $stats['leaveShiftConflicts'],
            'expenseTotal' => $stats['expenseTotal'],
            'expenseByCategory' => $stats['expenseByCategory'],
        ]);
    }

    public function reports(Request $request): View
    {
        $companyId = $request->user()?->company_id;
        $range = $request->string('range')->toString();
        $range = in_array($range, ['30d', '90d', '12m'], true) ? $range : '12m';
        $rangeStart = match ($range) {
            '30d' => Carbon::now()->subDays(30),
            '90d' => Carbon::now()->subDays(90),
            // include the current month plus the previous 11 months (12 total)
            default => Carbon::now()->startOfMonth()->subMonths(11),
        };

        $revenueReport = collect();
        $jobsByStatus = [];
        $topCustomers = collect();
        $leaveSummary = [];
        $expenseVsRevenue = collect();
        $revenueSixMonths = collect();
        $expenseSixMonths = collect();

        if (! $request->user() || $companyId === null) {
            return $this->renderReportsView(
                $range,
                $revenueReport,
                $jobsByStatus,
                $topCustomers,
                $leaveSummary,
                $expenseVsRevenue,
                self::EXPENSE_REVENUE_MONTH_WINDOW
            );
        }

        $invoiceMonthExpression = DateQueryHelper::monthExpression('updated_at');

        try {
            $revenueReport = Invoice::query()
                ->where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereDate('updated_at', '>=', $rangeStart->toDateString())
                ->selectRaw($invoiceMonthExpression . ' as month, SUM(total) as revenue')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } catch (\Throwable $e) {
            $revenueReport = collect();
        }

        try {
            $jobsByStatus = ServiceJob::query()
                ->where('company_id', $companyId)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        } catch (\Throwable $e) {
            $jobsByStatus = [];
        }

        try {
            $topCustomers = Invoice::query()
                ->where('invoices.company_id', $companyId)
                ->where('status', 'paid')
                ->whereDate('invoices.updated_at', '>=', $rangeStart->toDateString())
                ->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->where('customers.company_id', $companyId)
                ->selectRaw('customers.name, SUM(invoices.total) as total_paid')
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_paid')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $topCustomers = collect();
        }

        try {
            $leaveSummary = Leave::query()
                ->where('company_id', $companyId)
                ->whereMonth('start_date', Carbon::now()->month)
                ->whereYear('start_date', Carbon::now()->year)
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();
        } catch (\Throwable $e) {
            $leaveSummary = [];
        }

        $comparisonStart = $this->expenseRevenueStartDate();

        try {
            $revenueSixMonths = Invoice::query()
                ->where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereDate('updated_at', '>=', $comparisonStart->toDateString())
                ->selectRaw($invoiceMonthExpression . ' as month, SUM(total) as revenue')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } catch (\Throwable $e) {
            $revenueSixMonths = collect();
        }

        try {
            $expenseSixMonths = Expense::totalsByMonth($companyId, 6);
        } catch (\Throwable $e) {
            $expenseSixMonths = collect();
        }

        try {
            $comparisonMonths = $revenueSixMonths->pluck('month')
                ->merge($expenseSixMonths->pluck('month'))
                ->unique()
                ->sort()
                ->values();

            $expenseVsRevenue = $comparisonMonths->map(function ($month) use ($revenueSixMonths, $expenseSixMonths) {
                $revenueRow = $revenueSixMonths->firstWhere('month', $month);
                $expenseRow = $expenseSixMonths->firstWhere('month', $month);

                return [
                    'month' => $month,
                    'revenue' => (float) ($revenueRow?->revenue ?? 0),
                    'expense' => (float) ($expenseRow?->total ?? 0),
                ];
            });
        } catch (\Throwable $e) {
            $expenseVsRevenue = collect();
        }

        return $this->renderReportsView(
            $range,
            $revenueReport,
            $jobsByStatus,
            $topCustomers,
            $leaveSummary,
            $expenseVsRevenue,
            self::EXPENSE_REVENUE_MONTH_WINDOW
        );
    }

    /**
     * Prepare and render the reports view with chart-ready datasets.
     *
     * @return View
     */
    private function renderReportsView(
        string $range,
        Collection $revenueReport,
        array $jobsByStatus,
        Collection $topCustomers,
        array $leaveSummary,
        Collection $expenseVsRevenue,
        int $comparisonWindow = self::EXPENSE_REVENUE_MONTH_WINDOW
    ): View {
        $revenueLabels = $revenueReport->pluck('month')->all();
        $revenueValues = $revenueReport->pluck('revenue')->map(fn ($value) => (float) $value)->all();
        $jobStatusLabels = array_keys($jobsByStatus);
        $jobStatusValues = array_map(fn ($value) => (int) $value, array_values($jobsByStatus));
        $expenseMonths = $expenseVsRevenue->pluck('month')->all();
        $expenseRevenueSeries = $expenseVsRevenue->pluck('revenue')->map(fn ($value) => (float) $value)->all();
        $expenseTotals = $expenseVsRevenue->pluck('expense')->map(fn ($value) => (float) $value)->all();

        return view('default.panel.user.insights.reports', [
            'range' => $range,
            'revenueReport' => $revenueReport,
            'jobsByStatus' => $jobsByStatus,
            'topCustomers' => $topCustomers,
            'leaveSummary' => $leaveSummary,
            'expenseVsRevenue' => $expenseVsRevenue,
            'revenueLabels' => $revenueLabels,
            'revenueValues' => $revenueValues,
            'jobStatusLabels' => $jobStatusLabels,
            'jobStatusValues' => $jobStatusValues,
            'expenseMonths' => $expenseMonths,
            'expenseRevenueSeries' => $expenseRevenueSeries,
            'expenseTotals' => $expenseTotals,
            'expenseRevenueWindow' => $comparisonWindow,
        ]);
    }

    private function expenseRevenueStartDate(): Carbon
    {
        return Carbon::now()
            ->startOfMonth()
            ->subMonths(self::EXPENSE_REVENUE_MONTH_WINDOW - 1);
    }
}
