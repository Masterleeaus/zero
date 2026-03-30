<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Quote;
use App\Models\Work\Site;
use App\Services\Money\QuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuoteController extends CoreController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::query()->with('customer');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(static function ($builder) use ($search) {
                $builder->where('quote_number', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        $quotes = $query->latest('issue_date')->latest()->paginate(10)->withQueryString();

        return view('default.panel.user.money.quotes.index', [
            'quotes'  => $quotes,
            'filters' => [
                'status' => $status ?? '',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        return view('default.panel.user.money.quotes.show', [
            'quote' => $quote->load(['customer', 'invoices', 'items']),
            'sites' => $this->sites(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quote::class);

        return view('default.panel.user.money.quotes.form', [
            'quote'     => new Quote(),
            'customers' => $this->customers(),
            'sites'     => $this->sites(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        $data = $this->validated($request);

        $quote = Quote::create([
            ...$data,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'status'     => 'draft',
        ]);

        $this->syncQuoteItems($quote, $request);

        return redirect()->route('dashboard.money.quotes.show', $quote)
            ->with('status', __('Quote created.'));
    }

    public function edit(Quote $quote): View
    {
        $this->authorize('update', $quote);

        return view('default.panel.user.money.quotes.form', [
            'quote'     => $quote,
            'customers' => $this->customers(),
            'sites'     => $this->sites(),
        ]);
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $data = $this->validated($request, ignoreId: $quote->id);
        $quote->update($data);
        $this->syncQuoteItems($quote, $request);

        return redirect()->route('dashboard.money.quotes.show', $quote)
            ->with('status', __('Quote updated.'));
    }

    public function updateStatus(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('changeStatus', $quote);

        $request->validate([
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected', 'expired'])],
        ]);

        $quote->update(['status' => $request->string('status')->toString()]);

        if ($quote->status === 'accepted') {
            event(new \App\Events\QuoteAccepted($quote));
        }

        return back()->with('status', __('Quote status updated.'));
    }

    public function convertToJob(Request $request, Quote $quote, QuoteService $service): RedirectResponse
    {
        $this->authorize('convert', $quote);

        $data = $request->validate([
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
        ]);

        DB::transaction(function () use ($service, $quote, $data, $request) {
            $service->convertToServiceJob($quote, $data['site_id'], $request->user());
            $quote->update(['status' => 'accepted']);
            event(new \App\Events\QuoteAccepted($quote));
        });

        return redirect()->route('dashboard.work.service-jobs.index')
            ->with('status', __('Service job created from quote.'));
    }

    public function convertToInvoice(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('convert', $quote);

        if ($quote->status !== 'accepted') {
            return back()->withErrors(__('Quote must be accepted before invoicing.'));
        }

        $invoice = DB::transaction(function () use ($quote, $request) {
            $quote->load('items');
            $invoice = \App\Models\Money\Invoice::create([
                'company_id'     => $quote->company_id,
                'created_by'     => $request->user()->id,
                'customer_id'    => $quote->customer_id,
                'quote_id'       => $quote->id,
                'invoice_number' => $quote->quote_number ? $quote->quote_number . '-INV' : null,
                'title'          => $quote->title,
                'status'         => 'issued',
                'issue_date'     => now(),
                'due_date'       => $quote->valid_until,
                'currency'       => $quote->currency,
                'subtotal'       => $quote->subtotal,
                'tax'            => $quote->tax,
                'total'          => $quote->total,
                'balance'        => $quote->total,
                'notes'          => $quote->notes,
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'company_id' => $invoice->company_id,
                    'created_by' => $request->user()->id,
                    'description'=> $item->description,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate'   => $item->tax_rate,
                    'line_total' => $item->line_total,
                    'sort_order' => $item->sort_order,
                ]);
            }

            $invoice->recomputeTotalsFromItems();
            $invoice->refresh();

            event(new \App\Events\InvoiceIssued($invoice));

            return $invoice;
        });

        return redirect()->route('dashboard.money.invoices.show', $invoice)
            ->with('status', __('Invoice created from quote.'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'quote_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('quotes', 'quote_number')->ignore($ignoreId),
            ],
            'title'       => ['nullable', 'string', 'max:150'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'site_id'     => ['nullable', 'integer', 'exists:sites,id'],
            'issue_date'  => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency'    => ['nullable', 'string', 'max:10'],
            'subtotal'    => ['required', 'numeric', 'min:0'],
            'tax'         => ['required', 'numeric', 'min:0'],
            'total'       => ['required', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'checklist_template_raw' => ['nullable', 'string'],
            'items'       => ['sometimes', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity'    => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price'  => ['required_with:items', 'numeric', 'min:0'],
            'items.*.tax_rate'    => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total'  => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($data['checklist_template_raw'])) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $data['checklist_template_raw']);
            $data['checklist_template'] = array_values(array_filter(array_map('trim', $lines), static fn($line) => $line !== ''));
            unset($data['checklist_template_raw']);
        }

        return $data;
    }

    private function syncQuoteItems(Quote $quote, Request $request): void
    {
        $items = collect($request->input('items', []));
        if ($items->isEmpty()) {
            return;
        }

        $quote->items()->delete();
        foreach ($items as $index => $item) {
            $lineSubtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $quote->items()->create([
                'company_id' => $quote->company_id,
                'created_by' => $request->user()->id,
                'description'=> $item['description'] ?? '',
                'quantity'   => $item['quantity'] ?? 0,
                'unit_price' => $item['unit_price'] ?? 0,
                'tax_rate'   => $item['tax_rate'] ?? 0,
                'line_total' => $item['line_total'] ?? $lineSubtotal,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }

        $quote->load('items');
        $quote->recomputeTotalsFromItems();
    }

    private function customers()
    {
        return \App\Models\Crm\Customer::query()
            ->where('company_id', auth()->user()?->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function sites()
    {
        return Site::query()
            ->where('company_id', auth()->user()?->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

}
