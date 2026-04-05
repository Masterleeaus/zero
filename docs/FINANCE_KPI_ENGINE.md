# FINANCE KPI ENGINE

## Service
`app/Services/TitanMoney/FinancialKpiService.php`

## Purpose
Generates key financial performance indicators for a company within an optional date window.

## Method

### `compute(int $companyId, ?Carbon $from, ?Carbon $to): array`
Computes all KPIs. Defaults to last 30 days if no period supplied.

## KPIs Generated

| KPI | Description |
|---|---|
| `gross_margin_pct` | Gross margin as percentage of revenue |
| `net_margin_pct` | Net margin as percentage of revenue |
| `labor_ratio` | Labor cost / total job cost |
| `material_ratio` | Material cost / total job cost |
| `revenue_per_team` | Total revenue / distinct teams active |
| `revenue_per_job` | Total revenue / job count |
| `cost_per_site` | Total cost / distinct sites with allocations |
| `avg_job_profit` | Gross margin / job count |
| `cash_buffer_days` | Cash on hand / daily burn rate |

## Output

```json
{
  "period_start": "2026-03-06",
  "period_end": "2026-04-05",
  "company_id": 1,
  "gross_margin_pct": 40.0,
  "net_margin_pct": 25.0,
  "labor_ratio": 0.55,
  "material_ratio": 0.20,
  "revenue_per_team": 12000.00,
  "revenue_per_job": 3000.00,
  "cost_per_site": 2500.00,
  "avg_job_profit": 1200.00,
  "cash_buffer_days": 45,
  "total_revenue": 24000.00,
  "total_cost": 14400.00,
  "total_expenses": 3600.00,
  "gross_margin": 9600.00,
  "net_margin": 6000.00
}
```

## Tenancy
All queries use `where('company_id', $companyId)` or `withoutGlobalScope('company')` with explicit company filter.
