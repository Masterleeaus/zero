# Inventory Host Audit

## Overview
This document records what the host system already has that is inventory-relevant.

## Equipment Domain (`app/Models/Equipment/`)
- `Equipment.php` — serialised equipment catalogue, product/serial lifecycle, site/customer/job linkages. Has company_id, created_by, SoftDeletes. Status: in_stock|installed|removed|retired|lost.
- `EquipmentMovement.php` — tracks equipment moves between locations.
- `EquipmentWarranty.php` — warranty records.
- `InstalledEquipment.php` — installed asset records.
- `WarrantyClaim.php` — claims against warranties.
- **Relevance**: Equipment represents physical assets. Procurement of new equipment (purchase orders) should be linkable via `inventory_item_id` FK on equipment table.

## ServiceJob Domain (`app/Models/Work/ServiceJob.php`)
- Core field service job model with BelongsToCompany + OwnedByUser.
- Linked to Equipment, Premises, Invoice, Customer.
- **Relevance**: Jobs consume materials/parts. The `job_material_usage` table bridges service jobs to inventory items.

## Money/Finance Domain (`app/Models/Money/`)
- `Invoice.php` — customer invoices.
- `Payment.php` — payments against invoices.
- `Expense.php` — expense tracking.
- **Relevance**: Purchase orders feed into payables. Supplier payments connect to Money domain.

## CRM Domain (`app/Models/Crm/Customer.php`)
- Customer entity with company_id, address, contact info.
- **Relevance**: Supplier model mirrors Customer structure (name, email, phone, address, tax_number, payment_terms).

## Existing Migrations
- Latest prefix: `2026_04_03_500100`
- Inventory migrations use range: `2026_04_03_700100`–`2026_04_03_700999`

## Summary Table

| Domain | Model | Inventory Relevance |
|--------|-------|---------------------|
| Equipment | Equipment.php | Asset procurement linkage |
| Work | ServiceJob.php | Material consumption |
| Money | Invoice.php | Payables integration |
| CRM | Customer.php | Supplier entity pattern |
