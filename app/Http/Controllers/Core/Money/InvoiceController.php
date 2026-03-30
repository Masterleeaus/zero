<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends CoreController
{
    public function index(Request $request): View
    {
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
        return view('default.panel.user.money.invoices.show', [
            'invoice' => $invoice->load(['customer', 'quote', 'payments']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('default.panel.user.money.invoices.form', [
            'invoice'   => new Invoice(),
            'customers' => $this->customers(),
            'quotes'    => $this->quotes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $invoice = Invoice::create([
            ...$data,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'balance'    => $data['total'],
            'status'     => $data['status'] ?? 'draft',
        ]);

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
        $this->authorizeCompany($invoice->company_id);

        return view('default.panel.user.money.invoices.form', [
            'invoice'   => $invoice,
            'customers' => $this->customers(),
            'quotes'    => $this->quotes(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($invoice->company_id);

        $previousStatus = $invoice->status;
        $data = $this->validated($request, ignoreId: $invoice->id);
        $invoice->update($data);
        $invoice->recomputeBalance();

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
        $this->authorizeCompany($invoice->company_id);
        $invoice->update(['status' => 'paid', 'balance' => 0, 'paid_amount' => $invoice->total]);
        event(new \App\Events\InvoicePaid($invoice));

        return back()->with('status', __('Invoice marked as paid.'));
    }

    public function markOverdue(Invoice $invoice): RedirectResponse
    {
        $this->authorizeCompany($invoice->company_id);
        $invoice->update(['status' => 'overdue']);

        return back()->with('status', __('Invoice marked as overdue.'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'invoice_number' => [
                'required',
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
            'subtotal'    => ['required', 'numeric', 'min:0'],
            'tax'         => ['required', 'numeric', 'min:0'],
            'total'       => ['required', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'status'      => ['nullable', Rule::in(['draft', 'issued', 'partial', 'paid', 'overdue', 'void'])],
        ]);
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

    private function authorizeCompany(int $companyId): void
    {
        if (auth()->user()?->company_id !== $companyId) {
            abort(403);
        }
    }
}
