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
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            'leaveTotals' => $leaveTotals,
            'upcomingLeave' => $upcomingLeave,
            'leaveShiftConflicts' => $leaveShiftConflicts,
            'expenseTotal' => $expenseTotal,
            'expenseByCategory' => $expenseByCategory,
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
            default => Carbon::now()->startOfMonth()->subMonthsNoOverflow(11),
        };

        $revenueReport = collect();
        $jobsByStatus = [];
        $topCustomers = collect();
        $leaveSummary = [];
        $expenseVsRevenue = collect();
        $revenueSixMonths = collect();
        $expenseSixMonths = collect();

        try {
            $revenueReport = Invoice::query()
                ->where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereDate('updated_at', '>=', $rangeStart->toDateString())
                ->selectRaw($this->monthExpression('updated_at') . ' as month, SUM(total) as revenue')
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
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();
        } catch (\Throwable $e) {
            $leaveSummary = [];
        }

        $comparisonStart = Carbon::now()->startOfMonth()->subMonths(5);

        try {
            $revenueSixMonths = Invoice::query()
                ->where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereDate('updated_at', '>=', $comparisonStart->toDateString())
                ->selectRaw($this->monthExpression('updated_at') . ' as month, SUM(total) as revenue')
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
            $comparisonMonths = collect($revenueSixMonths->pluck('month')->merge($expenseSixMonths->pluck('month')))
                ->unique()
                ->sort()
                ->values();

            $expenseVsRevenue = $comparisonMonths->map(function ($month) use ($revenueSixMonths, $expenseSixMonths) {
                return [
                    'month' => $month,
                    'revenue' => (float) ($revenueSixMonths->firstWhere('month', $month)->revenue ?? 0),
                    'expense' => (float) ($expenseSixMonths->firstWhere('month', $month)->total ?? 0),
                ];
            });
        } catch (\Throwable $e) {
            $expenseVsRevenue = collect();
        }

        $revenueLabels = $revenueReport->pluck('month')->all();
        $revenueValues = $revenueReport->pluck('revenue')->map(fn ($value) => (float) $value)->all();
        $jobStatusLabels = array_keys($jobsByStatus);
        $jobStatusValues = array_map('intval', array_values($jobsByStatus));
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
        ]);
    }

    private function monthExpression(string $column): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }
}
