<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CreditNoteController extends CoreController
{
    public function index(): View
    {
        return $this->placeholder(
            __('Credit notes'),
            __('List of credit notes synced from WorkCore.')
        );
    }

    public function create(): View
    {
        return $this->placeholder(
            __('Create credit note'),
            __('Issue a credit note for an invoice.')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Credit note created.'),
        ]);
    }

    public function show(string $creditNote): View
    {
        return $this->placeholder(
            __('Credit note detail'),
            __('Details for credit note :note.', ['note' => $creditNote])
        );
    }

    public function edit(string $creditNote): View
    {
        return $this->placeholder(
            __('Edit credit note'),
            __('Update credit note :note.', ['note' => $creditNote])
        );
    }

    public function update(Request $request, string $creditNote): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Credit note :note updated.', ['note' => $creditNote]),
        ]);
    }

    public function destroy(string $creditNote): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Credit note :note voided.', ['note' => $creditNote]),
        ]);
    }

    public function applyToInvoice(Request $request, string $creditNote): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Credit note :note applied to invoice.', ['note' => $creditNote]),
        ]);
    }
}

