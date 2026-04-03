<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Account;
use App\Models\Money\JournalEntry;
use App\Models\Money\JournalLine;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalEntryController extends CoreController
{
    public function __construct(private readonly AccountingService $accounting)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JournalEntry::class);

        $entries = JournalEntry::query()
            ->with(['lines.account'])
            ->latest('entry_date')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.money.journal.index', compact('entries'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', JournalEntry::class);

        $accounts = Account::query()
            ->active()
            ->orderBy('type')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.money.journal.create', [
            'entry'    => new JournalEntry(),
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JournalEntry::class);

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'entry_date'  => ['required', 'date'],
            'reference'   => ['nullable', 'string', 'max:100'],
            'currency'    => ['nullable', 'string', 'max:10'],
            'lines'       => ['required', 'array', 'min:2'],
            'lines.*.account_id'  => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:500'],
            'lines.*.debit'       => ['required', 'numeric', 'min:0'],
            'lines.*.credit'      => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $entry = $this->accounting->createJournalEntry(
                companyId:   $request->user()->company_id,
                description: $validated['description'],
                entryDate:   $validated['entry_date'],
                lines:       $validated['lines'],
                reference:   $validated['reference'] ?? null,
                currency:    $validated['currency'] ?? 'AUD',
                createdBy:   $request->user()->id,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['lines' => $e->getMessage()])->withInput();
        }

        return redirect()->route('dashboard.money.journal.show', $entry)
            ->with('status', __('Journal entry created.'));
    }

    public function show(JournalEntry $journalEntry): View
    {
        $this->authorize('view', $journalEntry);

        $journalEntry->load(['lines.account']);

        return view('default.panel.user.money.journal.show', [
            'entry' => $journalEntry,
        ]);
    }
}
