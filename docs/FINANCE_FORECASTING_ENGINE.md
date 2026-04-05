# FINANCE FORECASTING ENGINE

## Service
`app/Services/TitanMoney/ForecastingService.php`

## Purpose
Generates revenue, cost, margin, and cash-runway forecasts by projecting historical trends forward.

## Inputs

| Input | Source |
|---|---|
| Historical revenue | Paid invoices in lookback window |
| Historical expenses | Job cost allocations in lookback window |
| Labor utilization | Payroll (paid) in lookback window |
| Scheduled revenue | Open invoices with future issue dates |
| Pending liabilities | Approved supplier bills + approved payrolls |

## Methods

### `generate(int $companyId, int $horizonDays = 90): array`
Main forecasting method. Dispatches `ForecastGenerated` event.

### `forecast30(int $companyId): array`
Shortcut for 30-day forecast.

### `forecast90(int $companyId): array`
Shortcut for 90-day forecast.

### `forecast12Month(int $companyId): array`
Shortcut for 12-month (365-day) forecast.

## Output

```json
{
  "company_id": 1,
  "generated_at": "2026-04-05 00:00:00",
  "horizon_days": 90,
  "revenue_forecast": 45000.00,
  "cost_forecast": 27000.00,
  "margin_forecast": 18000.00,
  "margin_pct_forecast": 40.0,
  "cash_runway_estimate": 120,
  "pending_liabilities": 5000.00,
  "daily_revenue_trend": 500.0,
  "daily_cost_trend": 300.0
}
```

## Events Emitted
- `App\Events\Money\ForecastGenerated` — dispatched on every `generate()` call

## Projections Supported
- 30-day
- 90-day
- 12-month

## Tenancy
All queries scoped by `company_id`.
