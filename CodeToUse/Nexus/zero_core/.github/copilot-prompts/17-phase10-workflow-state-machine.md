# Copilot Task: Phase 10 — Workflow State Machine Completion

## Context
WorkCore merge Phase 10. The standard lifecycle is defined but the full cross-domain workflow transitions and automation hooks are incomplete. The canonical workflow per WORKCORE_MERGE.md is:

```
enquiry → quote_draft → quote_sent → quote_approved → scheduled → active → completed → invoiced → overdue → paid → recurring
```

## Current State
- Quote status transitions: draft/sent/approved/converted ✅
- Invoice status transitions: draft/issued/partial/paid/overdue/void ✅
- ServiceJob status transitions: scheduled/active/completed/cancelled ✅
- Quote→ServiceJob conversion: exists ✅
- Quote→Invoice conversion: partially done ⚠️ (see prompt 03)
- Recurring billing: ❌ missing
- Follow-up scheduling: ❌ missing
- Enquiry→Quote conversion: ❌ missing

## Your Task

### 1. Add Enquiry → Quote conversion
In `app/Http/Controllers/Core/Crm/EnquiryController.php`, add a `convertToQuote()` method:
```php
public function convertToQuote(Enquiry $enquiry): RedirectResponse
{
    $this->authorize('update', $enquiry);

    $quote = DB::transaction(function () use ($enquiry) {
        $q = Quote::create([
            'company_id'   => $enquiry->company_id,
            'created_by'   => auth()->id(),
            'customer_id'  => $enquiry->customer_id,
            'enquiry_id'   => $enquiry->id,  // add this FK if not present
            'title'        => $enquiry->subject ?? $enquiry->title,
            'status'       => 'draft',
            'currency'     => auth()->user()->company->currency ?? 'AUD',
        ]);

        $enquiry->update(['status' => 'quoted', 'quote_id' => $q->id]);

        return $q;
    });

    return redirect()->route('dashboard.money.quotes.edit', $quote)
        ->with('status', __('Quote created from enquiry.'));
}
```

Add route in `routes/core/crm.routes.php`:
```php
Route::post('enquiries/{enquiry}/convert-to-quote', [EnquiryController::class, 'convertToQuote'])
    ->name('dashboard.crm.enquiries.convert-to-quote');
```

Add FK columns if missing (migration):
- `enquiries.status` should include `'quoted'`
- `enquiries.quote_id` nullable FK to `quotes.id`
- `quotes.enquiry_id` nullable FK to `enquiries.id`

### 2. Add ServiceJob → Invoice direct conversion
In `ServiceJobController`, add `createInvoice()`:
```php
public function createInvoice(ServiceJob $job): RedirectResponse
{
    // Creates a draft invoice pre-populated with job details + any linked quote items
    $invoice = DB::transaction(function () use ($job) {
        $invoice = Invoice::create([
            'company_id'  => $job->company_id,
            'created_by'  => auth()->id(),
            'customer_id' => $job->customer_id ?? $job->site?->customer_id,
            'title'       => $job->title,
            'status'      => 'draft',
            'currency'    => 'AUD',
        ]);

        // If job has a linked quote, copy line items
        if ($job->quote) {
            foreach ($job->quote->items as $item) {
                $invoice->items()->create([...]);
            }
        }

        $job->update(['invoice_id' => $invoice->id]);
        return $invoice;
    });

    return redirect()->route('dashboard.money.invoices.edit', $invoice)
        ->with('status', __('Invoice created from service job.'));
}
```

### 3. Add Follow-Up scheduling for Enquiries
Add to `Enquiry` model:
- `follow_up_at` (datetime nullable)
- `follow_up_note` (text nullable)
- `follow_up_done` (boolean default false)

Scope: `Enquiry::dueFollowUps(int $companyId)` → returns enquiries where `follow_up_at <= now()` and `follow_up_done = false`

Add Artisan command `php artisan enquiries:notify-followups` that:
1. Finds all due follow-ups for all companies
2. Notifies the assigned user via `LiveNotification`
3. Register in Kernel: `->dailyAt('08:00')`

### 4. Centralise status transition logic in service layer
Ensure all status transitions go through the service layer, not directly in controllers.
Create or update `app/Services/Work/WorkflowService.php`:

```php
class WorkflowService
{
    public function transitionQuote(Quote $quote, string $toStatus): Quote { ... }
    public function transitionJob(ServiceJob $job, string $toStatus): ServiceJob { ... }
    public function transitionInvoice(Invoice $invoice, string $toStatus): Invoice { ... }
}
```

Each transition method should:
- Validate the transition is allowed (e.g., can't go from paid → draft)
- Fire the appropriate event
- Return the updated model

### 5. Create output doc
Create `docs/WORKFLOW_STATE_MAP.md` with:
- State machine diagram (text-based) for each entity
- List of allowed transitions
- Which events are fired on each transition
