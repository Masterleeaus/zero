<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);

        $accounts = Account::query()
            ->with('parent')
            ->orderBy('type')
            ->orderBy('code')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return view('default.panel.user.money.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        $parents = Account::query()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.money.accounts.form', [
            'account' => new Account(),
            'parents' => $parents,
            'types'   => Account::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Account::class);

        $data = $this->validated($request);

        Account::create([
            ...$data,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('dashboard.money.accounts.index')
            ->with('status', __('Account created.'));
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        $account->load(['children', 'parent']);

        return view('default.panel.user.money.accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        $parents = Account::query()
            ->active()
            ->whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.money.accounts.form', [
            'account' => $account,
            'parents' => $parents,
            'types'   => Account::TYPES,
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $data = $this->validated($request, $account->id);

        $account->update($data);

        return redirect()->route('dashboard.money.accounts.index')
            ->with('status', __('Account updated.'));
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('dashboard.money.accounts.index')
            ->with('status', __('Account deleted.'));
    }

    // -------------------------------------------------------------------------

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $companyId = $request->user()->company_id;

        $codeRule = ['nullable', 'string', 'max:20'];
        if ($request->filled('code')) {
            $codeRule[] = \Illuminate\Validation\Rule::unique('accounts')
                ->where('company_id', $companyId)
                ->ignore($ignoreId ?? 0);
        }

        return $request->validate([
            'code'        => $codeRule,
            'name'        => ['required', 'string', 'max:200'],
            'type'        => ['required', 'string', 'in:' . implode(',', Account::TYPES)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
            'parent_id'   => ['nullable', 'integer', 'exists:accounts,id'],
        ]);
    }
}
