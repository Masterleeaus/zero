# Inventory Overlap Matrix

## Classification:
- **A** = Host already has this, use host version
- **B** = Source extends host, merge/adapt
- **C** = Source adds new capability, integrate cleanly
- **D** = Source duplicates infrastructure, discard
- **E** = Deferred to later phase

| Feature | Host Has | Source Has | Classification | Action |
|---------|----------|------------|----------------|--------|
| Auth/Users | ✅ full stack | ✅ duplicate | D | Discard source auth |
| Roles/Permissions | ✅ full stack | ✅ duplicate | D | Discard source roles |
| Company/Tenant | ✅ BelongsToCompany | ✅ duplicate | D | Use host trait |
| Customer entity | ✅ Customer.php | ✅ Suppliers/ | A/B | Use host pattern for Supplier |
| Equipment | ✅ Equipment.php | — | A | Add inventory_item_id FK |
| ServiceJob | ✅ ServiceJob.php | ✅ FieldItems/ | B | Create job_material_usage bridge |
| Invoice/Payment | ✅ Money domain | ✅ Bills/Credits | A/C | Future: PO→Bill payables |
| Inventory Items | ❌ none | ✅ Inventory/ | C | Create InventoryItem |
| Warehouses | ❌ none | ✅ Inventory/ | C | Create Warehouse |
| Stock Movements | ❌ none | ✅ Inventory/ | C | Create StockMovement |
| Stocktakes | ❌ none | ✅ Inventory/ | C | Create Stocktake + Lines |
| Suppliers | ❌ none | ✅ Suppliers/ + Purchase/ | C | Create Supplier |
| Purchase Orders | ❌ none | ✅ Purchase/ | C | Create PurchaseOrder |
| PO Line Items | ❌ none | ✅ Purchase/ | C | Create PurchaseOrderItem |
| Audit Trail | ❌ none (per-domain) | ✅ Inventory/ | C | Create InventoryAudit |
| Bin Locations | ❌ none | ✅ WMSInventoryCore/ | E | Phase 2 |
| Reservations | ❌ none | ✅ WMSInventoryCore/ | E | Phase 2 |
| Batch Tracking | ❌ none | ✅ WMSInventoryCore/ | E | Phase 2 |
| Vendor Payments | ❌ none | ✅ Purchase/ | E | Phase 2 via Money domain |
