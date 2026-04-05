# FINANCE JOB PROFITABILITY DASHBOARD

## Service
`app/Services/TitanMoney/ProfitabilityService.php` (Finance Pass 3, extended in Pass 4)

## Purpose
Provides per-dimension margin analytics for the financial dashboard.

## Methods

| Method | Dimension | Filters |
|---|---|---|
| `forJob(ServiceJob $job)` | Per job | — |
| `forSite(int $siteId, ?Carbon $from, ?Carbon $to)` | Per site | Date range |
| `forTeam(int $teamId, ?Carbon $from, ?Carbon $to)` | Per team | Date range |
| `forCustomer(int $customerId, ?Carbon $from, ?Carbon $to)` | Per customer | Date range |
| `forPeriod(int $companyId, Carbon $from, Carbon $to)` | Company period | Date range |
| `calculateMargins(float $revenue, float $cost)` | Generic | — |

## Output Structure (all methods)

```json
{
  "gross_revenue": 10000.00,
  "gross_cost": 6000.00,
  "gross_margin": 4000.00,
  "margin_pct": 40.0
}
```

## Dashboard Endpoint
`GET /dashboard/money/job-profitability` → `dashboard.money.job-profitability.index`

Controller: `App\Http\Controllers\Core\Money\FinancialDashboardController::jobProfitability`

## Tenancy
All queries are `company_id` scoped via `BelongsToCompany` trait or explicit `where('company_id', ...)`.
