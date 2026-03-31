<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CreditNoteController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.money.credit-notes.index', [
            'creditNotes' => WorkcoreDemoData::creditNotes(),
            'filters'     => [
                'status' => request()->string('status')->toString() ?: '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.money.credit-notes.form', [
            'creditNote' => null,
            'items'      => WorkcoreDemoData::lineItemsSeed(),
            'statuses'   => ['draft', 'issued', 'applied', 'void'],
        ]);
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
        return view('default.panel.user.money.credit-notes.show', [
            'creditNote' => WorkcoreDemoData::creditNotes()->firstWhere('number', $creditNote)
                ?? WorkcoreDemoData::creditNotes()->first(),
        ]);
    }

    public function edit(string $creditNote): View
    {
        return view('default.panel.user.money.credit-notes.form', [
            'creditNote' => WorkcoreDemoData::creditNotes()->firstWhere('number', $creditNote),
            'items'      => WorkcoreDemoData::lineItemsSeed(),
            'statuses'   => ['draft', 'issued', 'applied', 'void'],
        ]);
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
