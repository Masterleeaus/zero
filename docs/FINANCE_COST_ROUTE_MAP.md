# Finance — Cost Route Map

**Date:** 2026-04-04  
**Controllers:** `JobCostingController`, `ProfitabilityController`

---

## Job Cost Allocations

| Name | Method | URI | Controller | Notes |
|------|--------|-----|------------|-------|
| `money.cost-allocations.index` | GET | `/money/cost-allocations` | `JobCostingController@index` | Lists allocations scoped to company |
| `money.cost-allocations.create` | GET | `/money/cost-allocations/create` | `JobCostingController@create` | Create form |
| `money.cost-allocations.store` | POST | `/money/cost-allocations` | `JobCostingController@store` | Persist new allocation |
| `money.cost-allocations.show` | GET | `/money/cost-allocations/{allocation}` | `JobCostingController@show` | View single allocation |

Authorization: `JobCostAllocationPolicy` enforced via `authorizeResource()` or explicit `$this->authorize()` calls.

---

## Profitability

| Name | Method | URI | Controller | Notes |
|------|--------|-----|------------|-------|
| `money.profitability.index` | GET | `/money/profitability` | `ProfitabilityController@index` | Company-level summary |
| `money.profitability.job` | GET | `/money/profitability/job/{job}` | `ProfitabilityController@job` | Per-job profitability |
| `money.profitability.by-period` | GET | `/money/profitability/by-period` | `ProfitabilityController@byPeriod` | Period-based profitability |

---

## Route Registration

Routes are registered within the `dashboard.money.*` route group in:

```
routes/web.php  (or routes/money.php if modular)
```

All routes require authentication middleware and are prefixed with the company context.
