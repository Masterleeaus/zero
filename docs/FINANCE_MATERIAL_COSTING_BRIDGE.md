# Finance — Material Costing Bridge

**Date:** 2026-04-04  
**Service:** `App\Services\TitanMoney\MaterialCostingService`

---

## Purpose

`MaterialCostingService` allocates material costs to jobs from supplier bill lines and inventory usage events. It delegates persistence to `JobCostingService`.

---

## Methods

### `costFromSupplierBillLine(SupplierBillLine $line): array`

Returns cost breakdown for a single bill line without writing to DB.

```php
$cost = $service->costFromSupplierBillLine($line);
// ['description' => '...', 'quantity' => 2.0, 'unit_cost' => 45.00, 'amount' => 90.00, 'supplier_id' => 3]
```

---

### `allocateBillLinesToJob(SupplierBill $bill, int $serviceJobId): array`

Iterates `$bill->lines`, allocating each to the given job. Returns array of `JobCostAllocation` records.

```php
$allocations = $service->allocateBillLinesToJob($bill, $job->id);
// Each allocation: source_type=supplier_bill_line, cost_type=material
```

---

### `allocateInventoryUsage(array $usageData, int $serviceJobId, int $companyId, int $createdBy): JobCostAllocation`

Creates a `JobCostAllocation` for inventory consumption. Sets `source_type='inventory_usage'` and `cost_type='material'`.

```php
$allocation = $service->allocateInventoryUsage([
    'description' => 'Copper pipe 10m',
    'amount'      => 180.00,
    'quantity'    => 10.0,
    'unit_cost'   => 18.00,
    'allocated_at' => today()->toDateString(),
], $job->id, $companyId, $userId);
```

---

### `materialCostForJob(ServiceJob $job): float`

Returns total `material` cost_type allocations for the job.

---

### `supplierCostForJob(ServiceJob $job): float`

Returns total `subcontractor` cost_type allocations for the job.

---

## Integration Points

| Source | How |
|--------|-----|
| `SupplierBillLine` | Via `allocateBillLinesToJob()` — references `supplier_bill_line` source type |
| Inventory (future) | Via `allocateInventoryUsage()` — references `inventory_usage` source type |
| `JobCostingService` | Delegates all DB writes |

---

## Known Limitations

- `allocateBillLinesToJob()` requires `$bill->lines` relationship to be loaded.  
  Currently `SupplierBill` exposes `items()` (→ `SupplierBillItem`); ensure `lines()` or equivalent is available when wiring this path.
- Full inventory consumption hooks (stock movement → cost allocation) are planned for Pass 4.
