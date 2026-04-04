# Finance — Cost UI Map

**Date:** 2026-04-04

---

## Cost Allocations

### Index View: `resources/views/money/cost-allocations/index.blade.php`

- Lists `JobCostAllocation` records for the authenticated company
- Filterable by job, cost type, date range
- Columns: Date, Job, Cost Type, Source, Amount, Posted

### Show View: `resources/views/money/cost-allocations/show.blade.php`

- Detail view for a single allocation
- Shows source record link (expense / timesheet / payroll / bill line)
- Shows journal entry link if posted

### Create View: `resources/views/money/cost-allocations/create.blade.php`

- Manual allocation form
- Fields: Job (select), Cost Type (select), Amount, Description, Date
- Submits to `money.cost-allocations.store`

---

## Profitability

### Index View: `resources/views/money/profitability/index.blade.php`

- Company-level profitability summary
- Shows gross cost, gross revenue, gross margin, margin %
- Period selector for date range filtering
- Breakdown by job (table), by site (optional), by team (optional)

---

## UI Integration Points

| Component | Route | Notes |
|-----------|-------|-------|
| ServiceJob detail page | `dashboard.fieldservice.jobs.show` | Link to cost allocations for job |
| Profitability dashboard | `money.profitability.index` | Finance module dashboard widget |
| Payroll run detail | `dashboard.money.payroll.show` | Link to payroll-sourced allocations |
