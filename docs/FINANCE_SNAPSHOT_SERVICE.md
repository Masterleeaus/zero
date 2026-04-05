# FINANCE SNAPSHOT SERVICE

## Service
`app/Services/TitanMoney/FinancialSnapshotService.php`

## Purpose
Produces a real-time financial health snapshot for a company. All data is scoped by `company_id`.

## Outputs

| Field | Description |
|---|---|
| `cash_on_hand` | Sum of all received payments |
| `receivables_total` | Balance on outstanding (non-paid, non-cancelled) invoices |
| `payables_total` | Total approved supplier bills |
| `wages_liability_estimate` | Approved-but-unpaid payroll totals |
| `supplier_liability` | Mirrors payables_total (approved bills) |
| `job_cost_outstanding` | Unposted job cost allocations |
| `unbilled_work_estimate` | Total invoice value minus paid_amount |
| `gross_margin_estimate` | Paid invoice revenue minus total job cost |

## Methods

### `snapshot(int $companyId, ?Carbon $asAt = null): array`
Full snapshot as at a given date (defaults to now). Dispatches `FinancialSnapshotUpdated` event.

### `periodSnapshot(int $companyId, Carbon $from, Carbon $to): array`
Period-bounded snapshot with cash_in / cash_out / net_cash.

### `rollingSnapshot(int $companyId, int $days = 30): array`
Rolling N-day snapshot using `periodSnapshot`.

## Events Emitted
- `App\Events\Money\FinancialSnapshotUpdated` — dispatched on every `snapshot()` call

## Tenancy
All queries use `where('company_id', $companyId)`. Cross-company isolation verified in tests.
