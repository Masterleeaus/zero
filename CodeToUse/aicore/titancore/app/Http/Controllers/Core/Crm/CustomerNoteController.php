<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerNoteController extends CoreController
{
    public function index(string $customer): View
    {
        return $this->placeholder(
            __('Customer notes'),
            __('Notes for customer :customer will appear here.', ['customer' => $customer])
        );
    }

    public function store(Request $request, string $customer): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Note added to customer :customer.', ['customer' => $customer]),
        ]);
    }

    public function update(Request $request, string $customer, string $note): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Note :note updated.', ['note' => $note]),
        ]);
    }

    public function destroy(string $customer, string $note): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Note :note removed.', ['note' => $note]),
        ]);
    }

    public function togglePin(string $customer, string $note): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Note :note pin toggled.', ['note' => $note]),
        ]);
    }
}

