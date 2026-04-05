# FINANCE DASHBOARD ROUTE MAP

## Route File
`routes/core/money.routes.php`

## Registered Dashboard Routes (Finance Pass 4)

All routes are under the `dashboard.money.*` prefix group (requires auth + throttle).

| Route Name | Method | URL | Controller Method |
|---|---|---|---|
| `dashboard.money.dashboard.index` | GET | `/dashboard/money/dashboard` | `FinancialDashboardController::dashboard` |
| `dashboard.money.cashflow.index` | GET | `/dashboard/money/cashflow` | `FinancialDashboardController::cashflow` |
| `dashboard.money.forecast.index` | GET | `/dashboard/money/forecast` | `FinancialDashboardController::forecast` |
| `dashboard.money.kpis.index` | GET | `/dashboard/money/kpis` | `FinancialDashboardController::kpis` |
| `dashboard.money.job-profitability.index` | GET | `/dashboard/money/job-profitability` | `FinancialDashboardController::jobProfitability` |

## Controller
`app/Http/Controllers/Core/Money/FinancialDashboardController.php`

## Services Consumed

| Endpoint | Services |
|---|---|
| dashboard | `FinancialSnapshotService`, `FinancialKpiService` |
| cashflow | `CashflowService` |
| forecast | `ForecastingService` |
| kpis | `FinancialKpiService` |
| job-profitability | `ProfitabilityService` |

## Middleware
Auth + throttle:120,1 (inherited from core route group).
