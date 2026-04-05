# FINANCE DASHBOARD UI MAP

## View Directory
`resources/views/default/panel/user/money/dashboard/`

## Views

| File | Route | Description |
|---|---|---|
| `index.blade.php` | `dashboard.money.dashboard.index` | Main financial overview — snapshot widgets + 30-day KPI tiles |
| `cashflow.blade.php` | `dashboard.money.cashflow.index` | Cashflow table — weekly/monthly/rolling 90-day |
| `forecast.blade.php` | `dashboard.money.forecast.index` | 30/90/12-month forecast panels |
| `kpis.blade.php` | `dashboard.money.kpis.index` | Full KPI tile grid with period filter |
| `job-profitability.blade.php` | `dashboard.money.job-profitability.index` | Period profitability summary |

## Variables Passed

### `index.blade.php`
- `$snapshot` — from `FinancialSnapshotService::snapshot()`
- `$kpis` — from `FinancialKpiService::compute()`

### `cashflow.blade.php`
- `$weekly` — 4-week projection array
- `$monthly` — 3-month projection array
- `$rolling90` — 90-day rolling with totals

### `forecast.blade.php`
- `$forecast30` — 30-day forecast array
- `$forecast90` — 90-day forecast array
- `$forecast12m` — 12-month forecast array

### `kpis.blade.php`
- `$kpis` — full KPI set from `FinancialKpiService::compute()`

### `job-profitability.blade.php`
- `$byPeriod` — margins from `ProfitabilityService::forPeriod()`
- `$from`, `$to` — Carbon date bounds

## CSS Framework
Tailwind CSS (matches existing host view patterns).
