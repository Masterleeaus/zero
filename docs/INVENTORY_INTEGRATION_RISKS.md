# Inventory Integration Risks

## Schema Collision Risks

### equipment table
- **Risk**: Adding `inventory_item_id` column to existing `equipment` table.
- **Mitigation**: Use `Schema::hasColumn()` check in migration. Column is nullable.
- **Impact if collision**: Migration fails, app still boots.

### service_jobs table
- **Risk**: Could add direct inventory FK but opted for separate `job_material_usage` table.
- **Mitigation**: Bridge table approach avoids modifying existing service_jobs schema.
- **Impact**: Zero collision risk.

### stock_movements — morph columns
- **Risk**: `moveable_type`/`moveable_id` morph — must use consistent class names.
- **Mitigation**: Use full class name `App\Models\Work\ServiceJob` as morph type.
- **Impact if wrong**: Polymorphic queries break silently.

## Naming Conflicts

### suppliers table
- **Risk**: Source Purchase/ module calls the entity "vendor". Host CRM has Customer.
- **Mitigation**: Use `suppliers` table name (not `vendors`). Supplier entity is distinct from Customer.
- **Impact**: No conflict.

### warehouses table
- **Risk**: May conflict with WMS or logistics modules if added later.
- **Mitigation**: Table name `warehouses` is standard. Keep `company_id` scope.
- **Impact**: Low risk.

## Tenancy Risks
- **Risk**: All models must have `company_id` + BelongsToCompany. If a model is created without company context (e.g., in a seed/job), company scoping fails.
- **Mitigation**: All factories should include `company_id`. Services accept company_id explicitly.

## Service Composition Risk
- **Risk**: PurchaseOrderService calls StockService. If StockService has a DB error, PO receipt partially fails.
- **Mitigation**: Wrap receivePurchaseOrder in a DB transaction.

## Route Naming Risk
- **Risk**: Route name collisions with future extensions.
- **Mitigation**: All routes prefixed `dashboard.inventory.*`. No conflicts with existing `work.`, `money.`, `crm.` namespaces.

## Missing Source Features (Deferred)
- Bin locations (WMSInventoryCore) — complex, deferred
- Batch/lot tracking — deferred
- Vendor payment workflow — deferred (integrate with Money domain in Phase 2)
- Stock reservations — deferred
