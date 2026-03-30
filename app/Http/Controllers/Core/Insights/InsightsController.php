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
use Illuminate\Support\Facades\DB;

class InsightsController extends CoreController
{
    public function overview(): View
    {
        $companyId = auth()->user()?->company_id;

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

        return view('default.panel.user.insights.overview', [
            'enquiryCount' => $enquiries,
            'customerCount'=> $customers,
            'activeSites'  => $sites,
            'jobStatus'    => $jobStatus,
            'quoteStatus'  => $quoteStatus,
            'invoiceStatus'=> $invoiceStatus,
            'overdueInvoices' => $overdueInvoices,
            'outstandingBalance' => $outstandingBalance,
            'paymentsTotal' => $paymentsTotal,
            'quoteToJobCount' => $quoteToJobCount,
            'quoteToInvoiceCount' => $quoteToInvoiceCount,
            'companyId' => $companyId,
        ]);
    }

    public function reports(): View
    {
        return $this->placeholder(__('Reports'), __('Reports scoped to the current company.'));
    }
}
