<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Account;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JournalEntryController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store   = $this->getActiveStore($request);
        $entries = JournalEntry::where('store_id', $store->id)
            ->with('lines.account', 'user')
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        $accounts = Account::where('store_id', $store->id)->orderBy('code')->get();

        return view('admin.journal.index', compact('entries', 'accounts'));
    }

    public function store(Request $request)
    {
        $activeStore = $this->getActiveStore($request);

        $request->validate([
            'date'        => 'required|date',
            // Scope reference uniqueness to this store only
            'reference'   => [
                'required',
                'string',
                Rule::unique('journal_entries')->where(fn($q) => $q->where('store_id', $activeStore->id)),
            ],
            'description'              => 'nullable|string',
            'lines'                    => 'required|array|min:2',
            'lines.*.account_id'       => 'required|exists:accounts,id',
            'lines.*.debit'            => 'required|numeric|min:0',
            'lines.*.credit'           => 'required|numeric|min:0',
        ]);

        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->with('error', 'Total Debits must equal Total Credits.')->withInput();
        }

        if ($totalDebit == 0) {
            return back()->with('error', 'Journal entry must have a non-zero value.')->withInput();
        }

        DB::transaction(function () use ($request, $activeStore) {
            $entry = JournalEntry::create([
                'store_id'    => $activeStore->id,
                'reference'   => $request->reference,
                'date'        => $request->date,
                'description' => $request->description,
                'user_id'     => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    $entry->lines()->create([
                        'account_id'  => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'debit'       => $line['debit'],
                        'credit'      => $line['credit'],
                    ]);

                    $account = Account::find($line['account_id']);
                    if (in_array($account->gl_type, ['asset', 'expense'])) {
                        $account->balance += $line['debit'];
                        $account->balance -= $line['credit'];
                    } else {
                        $account->balance += $line['credit'];
                        $account->balance -= $line['debit'];
                    }
                    $account->save();
                }
            }
        });

        return back()->with('success', 'Journal Entry created successfully.');
    }
}
