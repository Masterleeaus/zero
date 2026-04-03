# Workflow State Map

This document describes all allowed status transitions for the core TitanZero lifecycle:
**Enquiry → Quote → ServiceJob → Invoice → Payment**

---

## 1. Enquiry

```
open ──────► quoted
     └──────► closed
              converted-to-job
```

| From   | To                   | Trigger                                    |
|--------|----------------------|--------------------------------------------|
| open   | quoted               | `EnquiryController::convertToQuote()`      |
| open   | converted-to-job     | `EnquiryController::convertToServiceJob()` |
| open   | closed               | Manual status update                       |

---

## 2. Quote

```
draft ──────► sent
      ├──────► approved
      └──────► rejected / expired

sent ───────► accepted
     ├──────► approved
     ├──────► rejected
     └──────► expired

approved ───► accepted
         └──► converted

accepted ───► converted

rejected ───► draft   (re-open)
expired  ───► draft   (re-open)
converted ──► (terminal)
```

| From      | Allowed To                              | Event Fired       |
|-----------|-----------------------------------------|-------------------|
| draft     | sent, approved, rejected, expired       | —                 |
| sent      | accepted, rejected, expired, approved   | —                 |
| approved  | accepted, converted                     | —                 |
| accepted  | converted                               | `QuoteAccepted`   |
| rejected  | draft                                   | —                 |
| expired   | draft                                   | —                 |
| converted | *(none)*                                | —                 |

**Service**: `WorkflowService::transitionQuote(Quote $quote, string $newStatus)`

---

## 3. ServiceJob

```
scheduled ──► in_progress ──► completed
          └──► cancelled    └──► cancelled
```

| From        | Allowed To              | Event Fired    |
|-------------|-------------------------|----------------|
| scheduled   | in_progress, cancelled  | —              |
| in_progress | completed, cancelled    | `JobStarted`   |
| completed   | cancelled               | `JobCompleted` |
| cancelled   | *(none)*                | `JobCancelled` |

> `date_start` is auto-stamped on `→ in_progress` (if not already set).  
> `date_end` is auto-stamped on `→ completed` (if not already set).

**Service**: `WorkflowService::transitionJob(ServiceJob $job, string $newStatus)`

**Invoice creation**: `ServiceJobController::createInvoice()` — generates a draft invoice from a completed job, copies quote line items if the job is linked to a quote.

---

## 4. Invoice

```
draft ──────► issued ──────► paid
      └──────► cancelled     └──► cancelled

issued ─────► overdue ──────► paid
                         └──► cancelled
```

| From      | Allowed To                  | Event Fired      |
|-----------|-----------------------------|------------------|
| draft     | issued, cancelled           | —                |
| issued    | paid, overdue, cancelled    | `InvoiceIssued`  |
| overdue   | paid, cancelled             | —                |
| paid      | *(none)*                    | `InvoicePaid`    |
| cancelled | *(none)*                    | —                |

> `issue_date` is auto-stamped on `→ issued` (if not already set).

**Service**: `WorkflowService::transitionInvoice(Invoice $invoice, string $newStatus)`

---

## 5. Full Lifecycle Summary

```
Enquiry (open)
    │
    ├─[convertToQuote]──► Quote (draft) ──► sent ──► accepted ──► converted
    │                        │
    │                        └─[QuoteService::convertToServiceJob]
    │                                   │
    └─[convertToServiceJob]─────────────┘
                                        │
                                    ServiceJob (scheduled)
                                        │
                                        ├─► in_progress
                                        │
                                        └─► completed
                                               │
                                    [createInvoice / JobBillingService]
                                               │
                                           Invoice (draft)
                                               │
                                               ├─► issued
                                               │
                                               └─► paid
```

---

## 6. Follow-up Scheduling (Enquiries)

Enquiries support a follow-up reminder system:

| Column           | Type      | Purpose                              |
|------------------|-----------|--------------------------------------|
| `follow_up_at`   | datetime  | When the follow-up should happen     |
| `follow_up_note` | text      | Reminder note for the team           |
| `follow_up_done` | boolean   | Mark true once follow-up is complete |

**Artisan command**: `enquiries:notify-followups`  
**Schedule**: `dailyAt('08:00')`  
**Scope**: `Enquiry::scopeDueFollowUps($companyId)` — returns enquiries where `follow_up_at ≤ now()` and `follow_up_done = false`.

---

## 7. FK Relationships

| Column                    | Migration                                                     |
|---------------------------|---------------------------------------------------------------|
| `enquiries.quote_id`      | `2026_03_31_101100_add_workflow_columns_to_enquiries`         |
| `quotes.enquiry_id`       | `2026_03_31_101100_add_workflow_columns_to_enquiries`         |
| `service_jobs.invoice_id` | `2026_04_01_000200_add_module3_billing_fields`                |
