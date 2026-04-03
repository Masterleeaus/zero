# Copilot Task: Complete Quote → Invoice Conversion

## Context
Laravel 10 SaaS. Quotes can be converted to invoices. The `convertToInvoice()` method in `QuoteController` exists but may not be copying line items correctly, and the quote-to-invoice link needs validation.

## Files
- `app/Http/Controllers/Core/Money/QuoteController.php` — `convertToInvoice()` method
- `app/Models/Money/Quote.php`
- `app/Models/Money/Invoice.php`
- `app/Models/Money/QuoteItem.php`
- `app/Models/Money/InvoiceItem.php`

## Your Task

### 1. Read the current `convertToInvoice()` method in QuoteController

### 2. Ensure the method does ALL of the following inside a `DB::transaction()`:
```php
// a) Create the invoice from quote data
$invoice = Invoice::create([
    'company_id'     => $quote->company_id,
    'created_by'     => auth()->id(),
    'customer_id'    => $quote->customer_id,
    'quote_id'       => $quote->id,
    'invoice_number' => $this->nextInvoiceNumber($quote->company_id),
    'title'          => $quote->title,
    'status'         => 'draft',
    'issue_date'     => now()->toDateString(),
    'due_date'       => now()->addDays(30)->toDateString(),
    'currency'       => $quote->currency,
    'subtotal'       => $quote->subtotal,
    'tax'            => $quote->tax,
    'total'          => $quote->total,
    'paid_amount'    => 0,
    'balance'        => $quote->total,
    'notes'          => $quote->notes,
]);

// b) Copy every QuoteItem to InvoiceItem
foreach ($quote->items as $item) {
    $invoice->items()->create([
        'company_id'  => $quote->company_id,
        'description' => $item->description,
        'quantity'    => $item->quantity,
        'unit_price'  => $item->unit_price,
        'total'       => $item->total,
        'sort_order'  => $item->sort_order ?? 0,
    ]);
}

// c) Mark quote as converted
$quote->update(['status' => 'converted']);

// d) Fire event
event(new \App\Events\InvoiceIssued($invoice));
```

### 3. Add validation
Before conversion, check:
- Quote status must be `approved` or `sent` (not `draft` or already `converted`)
- Quote must have at least one line item (`$quote->items()->exists()`)
- Return a redirect back with error if checks fail

### 4. Add a `converted` status to Quote
In `app/Models/Money/Quote.php` ensure `'converted'` is a valid status value.
In the Quote validation in `QuoteController`, add `'converted'` to the allowed status `Rule::in(...)` list.

### 5. Update the quote show view
In `resources/views/default/panel/user/money/quotes/show.blade.php`:
- Add a "Convert to Invoice" button visible only when quote status is `approved` or `sent`
- Hide the button if status is already `converted`
- Show a link to the generated invoice if `$quote->invoice` relationship exists

## Constraints
- All within a single `DB::transaction()`
- Check authorization: `$this->authorize('update', $quote)` before conversion
- Redirect to the new invoice show page on success
