# Finance Pass 3 — Implementation Report

**Date:** 2026-04-04  
**Domain:** Finance / Money  
**Pass:** 3 — Expense Normalization, Job Costing, Labor, Material, Payroll Posting, Profitability

---

## What Was Built

Pass 3 introduced the **Job Costing layer** — a unified system for tracking all costs incurred against service jobs, sites, teams, and customers. It bridges expenses, timesheets, payroll, and supplier costs into a single `job_cost_allocations` table and adds profitability analysis.

---

## New Files Created

### Model
- `app/Models/Money/JobCostAllocation.php` — Canonical cost allocation model with `BelongsToCompany`, `SoftDeletes`, scopes, and constants

### Services
- `app/Services/TitanMoney/JobCostingService.php` — Allocates costs from all sources; calculates job totals
- `app/Services/TitanMoney/LaborCostingService.php` — Labor cost from `TimesheetSubmission` + `StaffProfile.hourly_rate`
- `app/Services/TitanMoney/PayrollPostingService.php` — Payroll journal payload builder and posting bridge
- `app/Services/TitanMoney/MaterialCostingService.php` — Material cost from supplier bill lines and inventory usage
- `app/Services/TitanMoney/ProfitabilityService.php` — Gross margin calculations for job/site/team/customer/period

### Events
- `app/Events/Money/TimesheetApproved.php`
- `app/Events/Money/PayrollInputFinalized.php`
- `app/Events/Money/MaterialIssuedToJob.php` (stub)
- `app/Events/Money/CostAllocationCreated.php` (stub)

### Listeners
- `app/Listeners/Money/PostTimesheetApprovedToJobCost.php`
- `app/Listeners/Money/PostPayrollInputFinalizedToLedger.php`

### Controllers
- `app/Http/Controllers/Core/Money/JobCostingController.php`
- `app/Http/Controllers/Core/Money/ProfitabilityController.php`

### Policy
- `app/Policies/JobCostAllocationPolicy.php`

### Migration
- `database/migrations/2026_04_04_600300_finance_pass3_expense_normalization_and_job_costing.php`

### Tests
- `tests/Feature/Money/FinancePass3Test.php`

---

## Migration Details

**Table created:** `job_cost_allocations`

Key columns: `company_id`, `service_job_id`, `site_id`, `team_id`, `customer_id`, `supplier_id`, `source_type`, `source_id`, `cost_type`, `amount`, `quantity`, `unit_cost`, `allocated_at`, `posted`, `journal_entry_id`

**Table modified:** `expenses`

Fields added: `cost_bucket`, `service_job_id` (FK), `site_id`, `team_id`, `supplier_id`, `reimbursable_to_customer`, `allocation_reference`

---

## Services Overview

| Service | Primary Method | Input | Output |
|---------|---------------|-------|--------|
| `JobCostingService` | `allocateManual()` | cost data array | `JobCostAllocation` |
| `LaborCostingService` | `costForTimesheetSubmission()` | `TimesheetSubmission` | `['hours', 'rate', 'cost']` |
| `PayrollPostingService` | `buildPostingPayload()` | `Payroll` | journal lines array |
| `MaterialCostingService` | `allocateInventoryUsage()` | usage data | `JobCostAllocation` |
| `ProfitabilityService` | `forJob()` | `ServiceJob` | `['gross_cost', 'gross_revenue', 'gross_margin', 'margin_pct']` |

---

## Integration Points

| System | How Integrated |
|--------|---------------|
| `Expense` | Extended with normalization fields; `allocateExpense()` creates allocation |
| `TimesheetSubmission` | `LaborCostingService` reads `total_hours`; `StaffProfile.hourly_rate` for rate |
| `Payroll` | `PayrollPostingService` reads `total_gross/net/tax` for journal generation |
| `SupplierBillLine` | `allocateSupplierBillLine()` creates material allocation |
| `Invoice` | `ProfitabilityService` sums invoice totals for revenue |
| `AccountingService` | `PayrollPostingService` delegates posting via `postPayrollApproved()` |
| `JournalEntry` | Posting status tracked via `source_type='payroll'` |

---

## Known Limitations

1. **Inventory consumption** — `MaterialIssuedToJob` event is a stub; full stock movement → cost allocation not yet wired (planned Pass 4).
2. **Payroll interface-ready** — `PayrollPostingService` generates payloads and calls `AccountingService`, but requires `AccountingService::postPayrollApproved()` to be fully implemented.
3. **`allocateBillLinesToJob`** — Depends on `$bill->lines` relationship; `SupplierBill` currently exposes `items()` (→ `SupplierBillItem`). Relationship naming alignment needed.
4. **No automatic reimbursable invoicing** — Reimbursable cost flagging is complete; auto-invoice generation for reimbursable items is planned for a future pass.
5. **No currency handling** — All amounts are single-currency decimal(12,2); multi-currency planned later.

---

## Next Steps

- Pass 4: Inventory consumption hooks (`StockMovement` → `MaterialIssuedToJob` → `JobCostAllocation`)
- Wire `CostAllocationCreated` event listeners (notifications, analytics)
- Add `allocateBillLinesToJob` relationship fix (`$bill->lines()` → `SupplierBillLine`)
- Build reimbursable → invoice auto-generation
- Add `JobCostAllocation` breakdown widget to `ServiceJob` detail view
