# FINANCE CASHFLOW ENGINE

## Service
`app/Services/TitanMoney/CashflowService.php`

## Purpose
Produces actual and projected cash flow data for weekly, monthly, and rolling 90-day windows.

## Data Sources

| Type | Source |
|---|---|
| Actual inflow | `payments` (received) |
| Actual outflow | `expenses` (approved) + `payrolls` (paid) |
| Projected inflow | Open invoices with `due_date` in the window |
| Projected outflow | Approved supplier bills + approved pending payrolls |

## Methods

### `forPeriod(int $companyId, Carbon $from, Carbon $to): array`
Returns actual + projected cash position for a specific date range.

**Returns:**
- `actual_inflow`, `actual_outflow`
- `projected_inflow`, `projected_outflow`
- `net_actual`, `net_projected`, `net_position`

### `weeklyProjection(int $companyId, int $weeks = 4): array`
Weekly cash projection for next N weeks.

### `monthlyProjection(int $companyId, int $months = 3): array`
Monthly cash projection for next N months.

### `rolling90Day(int $companyId): array`
Full rolling 90-day projection split into weekly buckets with totals summary.

## Tenancy
All queries use `where('company_id', $companyId)`.
