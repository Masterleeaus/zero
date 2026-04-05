# FINANCE REPORTING TENANCY

## Overview
All Finance Pass 4 services enforce `company_id` scoping at the query level.

## Enforcement Pattern

Every query in the following services includes explicit company filtering:

| Service | Mechanism |
|---|---|
| `FinancialSnapshotService` | `where('company_id', $companyId)` on every model query |
| `CashflowService` | `where('company_id', $companyId)` on every model query |
| `ForecastingService` | `where('company_id', $companyId)` on every model query |
| `FinancialKpiService` | `where('company_id', $companyId)` + `withoutGlobalScope` with explicit filter |
| `CostDriverAnalysisService` | `where('company_id', $companyId)` on all allocation queries |
| `FinancialSignalService` | Delegates to scoped services + explicit company filters |

## Controller Scoping
`FinancialDashboardController` always reads `$request->user()->company_id` and passes it to services.
No user can request another company's data via dashboard routes.

## Tests

Cross-company isolation is validated in `tests/Feature/Money/FinancePass4Test.php`:

- `test_snapshot_is_company_scoped` — snapshot never includes data from other company
- `test_cashflow_for_period_is_company_scoped` — cashflow inflow isolation
- `test_cost_driver_is_company_scoped` — cost breakdown isolation
- `test_kpi_is_company_scoped` — KPI revenue isolation
- `test_profitability_for_period_is_company_scoped` — margin cost isolation
