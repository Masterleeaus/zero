<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankAccountController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Bank accounts'),
            __('Company settlement accounts managed via WorkCore.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Add bank account'),
            __('Register a bank account for payouts.')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account created.'),
        ]);
    }

    public function show(string $account): View
    {
        return $this->placeholder(
            __('Bank account detail'),
            __('Details for bank account :account.', ['account' => $account])
        );
    }

    public function edit(string $account): View
    {
        return $this->placeholder(
            __('Edit bank account'),
            __('Update bank account :account.', ['account' => $account])
        );
    }

    public function update(Request $request, string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account updated.', ['account' => $account]),
        ]);
    }

    public function destroy(string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account removed.', ['account' => $account]),
        ]);
    }

    public function setDefault(string $account): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Bank account :account set as default.', ['account' => $account]),
        ]);
    }
}
