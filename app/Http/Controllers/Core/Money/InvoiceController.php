<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Invoice;
use App\Models\Money\InvoiceItem;
use App\Models\Money\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with(['customer', 'quote']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $invoices = $query->latest('issue_date')->latest()->paginate(10)->withQueryString();

        return view('default.panel.user.money.invoices.index', [
            'invoices' => $invoices,
            'filters'  => [
                'status' => $status ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        return view('default.panel.user.money.invoices.show', [
            'invoice' => $invoice->load(['customer', 'quote', 'payments', 'items']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        return view('default.panel.user.money.invoices.form', [
            'invoice'   => new Invoice(),
            'customers' => $this->customers(),
            'quotes'    => $this->quotes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $this->validated($request);

        $invoice = DB::transaction(function () use ($data, $request) {
            $invoice = Invoice::create([
                ...$data,
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
                'subtotal'   => 0,
                'tax'        => 0,
                'total'      => 0,
                'paid_amount'=> 0,
                'balance'    => 0,
                'status'     => $data['status'] ?? 'draft',
            ]);

            $invoice->invoice_number = $invoice->invoice_number ?: $this->nextInvoiceNumber($invoice->company_id);
            $invoice->save();

            $this->syncInvoiceItems($invoice, $request);
            $invoice->refresh();

            return $invoice;
        });

        if ($invoice->status === 'issued') {
            event(new \App\Events\InvoiceIssued($invoice));
        }
        if ($invoice->status === 'paid') {
            event(new \App\Events\InvoicePaid($invoice));
        }

        return redirect()->route('dashboard.money.invoices.show', $invoice)
            ->with('status', __('Invoice created.'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        return view('default.panel.user.money.invoices.form', [
            'invoice'   => $invoice,
            'customers' => $this->customers(),
            'quotes'    => $this->quotes(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $previousStatus = $invoice->status;
        $data = $this->validated($request, ignoreId: $invoice->id);
        DB::transaction(function () use ($invoice, $data, $request) {
            $invoice->update($data);
            $this->syncInvoiceItems($invoice, $request);
            $invoice->invoice_number = $invoice->invoice_number ?: $this->nextInvoiceNumber($invoice->company_id);
            $invoice->save();
            $invoice->recomputeBalance();
        });

        if ($invoice->status === 'issued' && $previousStatus !== 'issued') {
            event(new \App\Events\InvoiceIssued($invoice));
        }
        if ($invoice->status === 'paid' && $previousStatus !== 'paid') {
            event(new \App\Events\InvoicePaid($invoice));
        }

        return redirect()->route('dashboard.money.invoices.show', $invoice)
            ->with('status', __('Invoice updated.'));
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $this->authorize('markPaid', $invoice);

        if ($invoice->status !== 'paid') {
            $invoice->update(['status' => 'paid', 'balance' => 0, 'paid_amount' => $invoice->total]);
            event(new \App\Events\InvoicePaid($invoice));
        }

        return back()->with('status', __('Invoice marked as paid.'));
    }

    public function markOverdue(Invoice $invoice): RedirectResponse
    {
        $this->authorize('markOverdue', $invoice);
        $invoice->update(['status' => 'overdue']);

        return back()->with('status', __('Invoice marked as overdue.'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'invoice_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')->ignore($ignoreId),
            ],
            'title'       => ['nullable', 'string', 'max:150'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'quote_id'    => ['nullable', 'integer', 'exists:quotes,id'],
            'issue_date'  => ['nullable', 'date'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency'    => ['nullable', 'string', 'max:10'],
            'notes'       => ['nullable', 'string'],
            'status'      => ['nullable', Rule::in(['draft', 'issued', 'partial', 'paid', 'overdue', 'void'])],
            'items'       => ['sometimes', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total'  => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function syncInvoiceItems(Invoice $invoice, Request $request): void
    {
        $items = collect($request->input('items', []));
        if ($items->isEmpty()) {
            return;
        }

        $invoice->items()->delete();
        foreach ($items as $index => $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $lineSubtotal = $quantity * $unitPrice;
            $invoice->items()->create([
                'company_id' => $invoice->company_id,
                'created_by' => $request->user()->id,
                'description'=> $item['description'] ?? '',
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate'   => $item['tax_rate'] ?? 0,
                'line_total' => $lineSubtotal,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }

        $invoice->load('items');
        $invoice->recomputeTotalsFromItems();
        $invoice->recomputeBalance();
    }

    private function nextInvoiceNumber(int $companyId): string
    {
        $latest = Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotNull('invoice_number')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'INV-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function customers()
    {
        return \App\Models\Crm\Customer::query()
            ->where('company_id', auth()->user()?->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function quotes()
    {
        return Quote::query()
            ->where('company_id', auth()->user()?->company_id)
            ->orderByDesc('issue_date')
            ->get(['id', 'quote_number']);
    }

}
