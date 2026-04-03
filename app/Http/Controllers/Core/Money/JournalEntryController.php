<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Controller;
use App\Models\Money\Account;
use App\Models\Money\JournalEntry;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(private readonly AccountingService $accounting) {}

    public function index(): View
    {
        $entries  = JournalEntry::with('lines.account', 'user')
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        $accounts = Account::where('is_active', true)->orderBy('code')->orderBy('name')->get();

        return view('default.panel.user.money.journal.index', compact('entries', 'accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = Auth::user()?->company_id;

        $request->validate([
            'date'                   => 'required|date',
            'reference'              => [
                'required',
                'string',
                'max:100',
                Rule::unique('journal_entries')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'description'            => 'nullable|string|max:500',
            'lines'                  => 'required|array|min:2',
            'lines.*.account_id'     => 'required|exists:accounts,id',
            'lines.*.debit'          => 'required|numeric|min:0',
            'lines.*.credit'         => 'required|numeric|min:0',
        ]);

        try {
            $this->accounting->postJournalEntry(
                companyId:   $companyId,
                reference:   $request->reference,
                date:        $request->date,
                lines:       $request->lines,
                description: $request->description,
                createdBy:   Auth::id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', __('Journal entry posted successfully.'));
    }
}
