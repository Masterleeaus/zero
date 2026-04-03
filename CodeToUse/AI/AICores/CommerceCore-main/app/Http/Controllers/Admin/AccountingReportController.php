<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\AccountingService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class AccountingReportController extends Controller
{
    use ResolvesStore;

    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $store      = $this->getActiveStore($request);
        $startDate  = now()->startOfMonth();
        $endDate    = now()->endOfMonth();

        $summary = $this->accountingService->getSummary($store->id, $startDate, $endDate);

        return view('admin.reports.accounting', compact('summary', 'store'));
    }
}
