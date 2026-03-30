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

        $enquiries = Enquiry::query()->count();
        $customers = Customer::query()->count();
        $sites = Site::query()->where('status', 'active')->count();

        $jobStatus = ServiceJob::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $quoteStatus = Quote::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $invoiceStatus = Invoice::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $overdueInvoices = Invoice::query()
            ->whereNotIn('status', ['paid', 'void'])
            ->whereDate('due_date', '<', now())
            ->count();

        $outstandingBalance = (float) Invoice::query()->whereNotIn('status', ['paid', 'void'])->sum('balance');
        $paymentsTotal = (float) Payment::query()->sum('amount');
        $quoteToJobCount = ServiceJob::query()->whereNotNull('quote_id')->count();
        $quoteToInvoiceCount = Invoice::query()->whereNotNull('quote_id')->count();

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
