# FINANCE SIGNAL ENGINE

## Service
`app/Services/TitanMoney/FinancialSignalService.php`

## Purpose
Evaluates a company's financial position and emits alert events when defined thresholds are breached.

## Method

### `evaluate(int $companyId, array $thresholds = []): array`
Runs all signal checks. Returns array of triggered alerts. All threshold values are overridable.

## Default Thresholds

| Threshold | Default | Description |
|---|---|---|
| `cash_buffer_days_min` | 30 | Alert when cash buffer < N days |
| `margin_pct_min` | 10.0 | Alert when gross margin < N% |
| `supplier_spike_pct` | 50.0 | Alert when supplier cost increases > N% MoM |
| `labor_anomaly_pct` | 30.0 | Alert when labor cost increases > N% MoM |
| `expense_surge_pct` | 40.0 | Alert when expense increases > N% MoM |

## Alerts Generated

| Signal | Severity | Trigger |
|---|---|---|
| `low_cash_buffer` | warning / critical | Cash buffer below minimum |
| `margin_drop` | warning / critical | Margin below threshold |
| `cashflow_risk` | warning | Projected outflow > projected inflow |
| `supplier_liability_spike` | warning | Supplier cost spike MoM |
| `labor_cost_anomaly` | warning | Labor cost anomaly MoM |
| `expense_surge` | warning | Expense surge MoM |
| `negative_job_margin` | warning | Completed job with negative margin |

## Events Emitted

| Event | When |
|---|---|
| `FinancialRiskDetected` | Any alerts exist |
| `MarginDropDetected` | Margin below threshold |
| `MarginThresholdCrossed` | Margin is negative |
| `CashRunwayWarning` | Cash buffer below minimum |
| `CashflowRiskDetected` | Projected outflow > inflow |

## Tenancy
All queries use `where('company_id', $companyId)`.
