# Finance Cost Domain Discovery

**Date:** 2026-04-04  
**Pass:** Finance Pass 3

---

## Expenses

| Component | Location | Notes |
|-----------|----------|-------|
| `Expense` model | `app/Models/Money/Expense.php` | `BelongsToCompany`, `HasFactory`; `amount`, `expense_date`, `status`, `created_by`, `approved_by` |
| `ExpenseCategory` | `app/Models/Money/ExpenseCategory.php` | Category reference, linked via `expense_category_id` |
| `ExpenseController` | `app/Http/Controllers/Core/Money/ExpenseController.php` | CRUD + approval workflow |
| `ExpensePolicy` | `app/Policies/ExpensePolicy.php` | Company-scoped view/create/update/delete |

New Pass 3 fields added to `expenses` table:

| Field | Type | Purpose |
|-------|------|---------|
| `cost_bucket` | `string nullable` | Classification for job costing |
| `service_job_id` | `bigint nullable` | FK to `service_jobs` |
| `site_id` | `bigint nullable` | Optional site scope |
| `team_id` | `bigint nullable` | Optional team scope |
| `supplier_id` | `bigint nullable` | Supplier reference |
| `reimbursable_to_customer` | `boolean` | Customer billing flag |
| `allocation_reference` | `string nullable` | Cross-reference token |

---

## Timesheets

| Component | Location | Notes |
|-----------|----------|-------|
| `WeeklyTimesheet` | `app/Models/Work/WeeklyTimesheet.php` | Weekly aggregation |
| `TimesheetSubmission` | `app/Models/Work/TimesheetSubmission.php` | Per-user per-week: `user_id`, `week_start`, `total_hours`, `status`, `company_id` |
| `Timelog` | `app/Models/Work/Timelog.php` | Individual time entries |
| `TimesheetService` | Host service layer | Approval workflow, validation |

---

## Staff Profiles

| Component | Location | Notes |
|-----------|----------|-------|
| `StaffProfile` | `app/Models/Work/StaffProfile.php` | `user_id`, `company_id`, `hourly_rate` (decimal:2), `salary`, `status`, `pay_frequency` |

`StaffProfile.hourly_rate` is the primary source for labor costing calculations.

---

## Inventory

| Component | Location | Notes |
|-----------|----------|-------|
| `Supplier` | `app/Models/Inventory/Supplier.php` | `BelongsToCompany`; name, email, status |
| `PurchaseOrder` | `app/Models/Inventory/PurchaseOrder.php` | Linked to supplier + optional `service_job_id` |
| `InventoryItem` | `app/Models/Inventory/InventoryItem.php` | Stock master |
| `StockMovement` | `app/Models/Inventory/StockMovement.php` | Usage events; future cost hook |

---

## Service Jobs

| Component | Location | Notes |
|-----------|----------|-------|
| `ServiceJob` | `app/Models/Work/ServiceJob.php` | `BelongsToCompany`; `customer_id`, `site_id`, `team_id`, `status`, `actual_revenue` |

Finance relationships:
- `costAllocations()` → `hasMany(JobCostAllocation)` (via `service_job_id`)
- `invoices()` → linked via `Invoice.service_job_id`
- `purchaseOrders()` → linked via `PurchaseOrder.service_job_id`

---

## Existing Cost Tracking

| Component | Notes |
|-----------|-------|
| `JobCostEntry` | `app/Models/Money/JobCostEntry.php` — legacy per-entry cost record |
| `JobCostRecord` | Aggregated cost record (pre-Pass 3) |
| `JobFinancialSummary` | Summary view per job |
| `JobCostAllocation` | **New (Pass 3)** — canonical cost allocation model |

---

## Payroll

| Component | Location | Notes |
|-----------|----------|-------|
| `Payroll` | `app/Models/Money/Payroll.php` | `company_id`, `period_start`, `period_end`, `pay_date`, `total_gross`, `total_tax`, `total_net`, `status` |
| `PayrollLine` | `app/Models/Money/PayrollLine.php` | Per-staff line: `staff_profile_id`, `gross_pay`, `tax_amount`, `net_pay` |
| `PayrollService` | `app/Services/TitanMoney/PayrollService.php` | `createRun`, `addLine`, `approve`, `recordPayment` |
