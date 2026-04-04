<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountingService;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ResolvesStore;

    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $storeId = $store->id;

        $transactions = Transaction::where('store_id', $storeId)
            ->with('account')
            ->latest()
            ->paginate(20);

        $accounts = Account::where('store_id', $storeId)
            ->where('is_active', true)
            ->get();

        // Financial Analytics — scoped to this store
        $incomeMTD = Transaction::where('store_id', $storeId)
            ->where('type', 'income')
            ->where('transaction_date', '>=', now()->startOfMonth())
            ->sum('amount');

        $expenseMTD = Transaction::where('store_id', $storeId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', now()->startOfMonth())
            ->sum('amount');

        $categoryStats = Transaction::where('store_id', $storeId)
            ->selectRaw('category, SUM(amount) as total')
            ->where('transaction_date', '>=', now()->subDays(30))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        $cashFlowDaily = Transaction::where('store_id', $storeId)
            ->selectRaw('DATE(transaction_date) as date, SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as flow')
            ->where('transaction_date', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'accounts',
            'incomeMTD',
            'expenseMTD',
            'categoryStats',
            'cashFlowDaily'
        ));
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'account_id'  => 'required|exists:accounts,id',
            'type'        => 'required|in:income,expense',
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string',
            'reference'   => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $this->accountingService->recordTransaction(
            $validated['account_id'],
            $store->id,
            $validated['type'],
            $validated['amount'],
            $validated['category'],
            $validated['reference'] ?? null,
            $validated['description'] ?? null
        );

        return back()->with('success', 'Transaction recorded successfully.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('account');
        return view('admin.transactions.show', compact('transaction'));
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $store = $this->getActiveStore($request);
        if ($transaction->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'This transaction does not belong to your store.');
        }
        $transaction->delete();
        return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted.');
    }
}
