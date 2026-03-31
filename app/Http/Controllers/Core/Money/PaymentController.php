<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class PaymentController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with(['invoice', 'invoice.customer'])
            ->latest('paid_at')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.money.payments.index', compact('payments'));
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('record', [Payment::class, $invoice]);

        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:150'],
            'paid_at'   => ['nullable', 'date'],
        ]);

        if ($invoice->status === 'void') {
            return back()->withErrors(__('Cannot record payments against a void invoice.'));
        }

        $previousStatus = $invoice->status;

        DB::transaction(static function () use ($invoice, $validated, $request, $previousStatus): void {
            Payment::create([
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
                'invoice_id' => $invoice->id,
                ...$validated,
            ]);

            $invoice->refresh();
            $invoice->recomputeBalance();
            if ($invoice->balance <= 0 && $previousStatus !== 'paid') {
                $invoice->status = 'paid';
                event(new \App\Events\InvoicePaid($invoice));
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }
            $invoice->save();
        });

        return back()->with('status', __('Payment recorded.'));
    }
}
