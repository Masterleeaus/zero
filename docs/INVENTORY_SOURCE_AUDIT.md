# Inventory Source Audit

## Source: inventory.zip (extracted to /tmp/inventory_extract/)

### Module 1: Inventory/
Core inventory management:
- Items (products, SKUs, pricing, quantities)
- Warehouses (storage locations)
- Stock Movements (in/out/adjust/transfer)
- Stocktakes (physical counts with lines)
- Audit Trail

### Module 2: Purchase/
Rich procurement:
- Vendors/Suppliers
- Purchase Orders with line items
- Bills and credits
- Vendor payments
- PO receipt workflow

### Module 3: Suppliers/
Basic supplier entity:
- Name, contact, address
- Simpler than Purchase/ vendor model
- Subset of Purchase/ functionality

### Module 4: FieldItems/
FSM field item consumption:
- Links jobs to inventory items
- Tracks qty used per job
- Cost calculation for field usage
- Bridges FSM (ServiceJob) to Inventory

### Module 5: WMSInventoryCore/
Full Warehouse Management System:
- Products and categories
- Units of measure
- Bin locations (advanced sub-warehouse)
- Transfers between locations
- Adjustments
- Inventory counts
- Reservations
- Batch/lot tracking

## Integration Priority
1. **Core Inventory** (Inventory/ + Suppliers/ merged) — foundation layer
2. **Purchase** (Purchase/ streamlined) — procurement layer
3. **FieldItems** (adapted as job_material_usage) — FSM integration
4. **WMSInventoryCore** — deferred to Phase 2 (advanced WMS)
