# FINANCE COST DRIVER ENGINE

## Service
`app/Services/TitanMoney/CostDriverAnalysisService.php`

## Purpose
Breaks down cost sources from `JobCostAllocation` records into analysis buckets and ranks contributors to margin erosion.

## Cost Buckets

| Bucket | Source cost_types |
|---|---|
| `labor` | `labour` |
| `materials` | `material` |
| `supplier` | `subcontractor` |
| `overhead` | `equipment`, `overhead`, `admin` |
| `other` | `reimbursable`, unmapped types |

## Methods

### `breakdown(int $companyId, ?Carbon $from, ?Carbon $to): array`
Full company-level cost driver breakdown with optional date window.

**Returns:**
```json
{
  "totals": {"labor": 400.00, "materials": 300.00, ...},
  "percentages": {"labor": 50.0, "materials": 37.5, ...},
  "ranked_drivers": [
    {"driver": "labor", "amount": 400.00, "pct": 50.0},
    ...
  ],
  "total_cost": 800.00
}
```

### `forJob(int $companyId, int $jobId): array`
Same breakdown scoped to a single service job.

## Tenancy
All queries include `where('company_id', $companyId)`.
