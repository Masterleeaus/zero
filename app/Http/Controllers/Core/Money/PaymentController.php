<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends CoreController
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:150'],
            'paid_at'   => ['nullable', 'date'],
        ]);

        if ($invoice->company_id !== $request->user()->company_id) {
            abort(403);
        }

        DB::transaction(static function () use ($invoice, $validated, $request): void {
            Payment::create([
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
                'invoice_id' => $invoice->id,
                ...$validated,
            ]);

            $invoice->refresh();
            $invoice->recomputeBalance();
            if ($invoice->balance <= 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }
            $invoice->save();
        });

        return back()->with('status', __('Payment recorded.'));
    }
}
