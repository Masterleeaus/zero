# FSM Modules — fieldservice_sale + fieldservice_sale_agreement
## Integration Report

**Modules:** `fieldservice_sale` + `fieldservice_sale_agreement`
**Date:** 2026-04-03
**Status:** ✅ Merged

---

## Source Behaviour Summary

### fieldservice_sale (Odoo)
- `sale.order` gains `fsm_location_id` and `fsm_order_ids`
- Products gain `field_service_tracking` (none / sale / line) and `fsm_order_template_id`
- On SO confirmation with FSM-tracked lines: one FSM order per sale, or one per line
- `sale.order.line` gains `fsm_order_id`, `qty_delivered_method = field_service`

### fieldservice_sale_agreement (Odoo)
- Overrides `SaleOrder._prepare_fsm_values()` to propagate `agreement_id` to the FSM order

---

## Canonical Mapping

| Odoo entity | TitanZero entity |
|---|---|
| `sale.order` | `App\Models\Money\Quote` |
| `sale.order.line` | `App\Models\Money\QuoteItem` |
| `fsm.order` | `App\Models\Work\ServiceJob` |
| `fsm.location` | `App\Models\Premises\Premises` |
| `fsm.agreement` | `App\Models\Work\ServiceAgreement` |
| `product.field_service_tracking` | `QuoteItem.field_service_tracking` |

---

## Changes Delivered

### Database Migration
**`2026_04_03_500100_add_fieldservice_sale_columns.php`**

| Table | Column | Purpose |
|---|---|---|
| `service_jobs` | `sale_line_id` | FK to `quote_items.id` — which line generated this job |
| `quote_items` | `field_service_tracking` | `no` / `sale` / `line` — job generation behaviour |
| `quote_items` | `service_tracking_type` | Freeform type label |
| `quotes` | `premises_id` | Physical service delivery location |
| `service_agreements` | `originating_quote_id` | Quote that activated the agreement |

---

### Models Updated

#### `App\Models\Money\QuoteItem`
- Added: `TRACKING_NONE`, `TRACKING_SALE`, `TRACKING_LINE` constants
- Added: `field_service_tracking`, `service_tracking_type` to `$fillable`
- Added: `serviceJobs()` HasMany relationship
- Added: `generatesFieldWork(): bool`

#### `App\Models\Money\Quote`
- Added: `premises_id` to `$fillable`
- Added: `premises()` BelongsTo relationship
- Added: `toServiceExecutionCandidates(): Collection` — quote lines eligible for job generation
- Added: `createsFieldWork(): bool`
- Added: `coversAgreementPlan(): bool`

#### `App\Models\Work\ServiceJob`
- Added: `sale_line_id` to `$fillable`
- Added: `quoteItem()` BelongsTo relationship
- Added: `originatingSale(): ?Quote`
- Added: `saleLineContext(): array`

#### `App\Models\Work\ServiceAgreement`
- Added: `originating_quote_id` column (via migration)
- Added: `originatingSale(): ?Quote`
- Added: `saleCoverageSummary(): array`

#### `App\Models\Work\ServicePlan`
- Added: `originatingSale(): ?Quote`

#### `App\Models\Crm\Customer`
- Added: `soldServiceJobs(): Collection`
- Added: `soldAgreements(): Collection`
- Added: `saleToServiceTimeline(): Collection`

---

### New Service
**`App\Services\Work\FieldServiceSaleService`**

| Method | Behaviour |
|---|---|
| `markAsFieldServiceSale(Quote)` | Fires `FieldServiceSaleCreated` |
| `approveQuote(Quote)` | Fires `FieldServiceSaleApproved` |
| `convertQuoteToJobs(Quote)` | Creates ServiceJobs from eligible QuoteItems (sale or line tracking) |
| `convertQuoteToServicePlan(Quote, ServiceAgreement)` | Creates ServicePlan linked to agreement |
| `activateAgreementFromQuote(Quote, ServiceAgreement)` | Activates or extends agreement; fires appropriate events |
| `createAgreementFromQuote(Quote)` | Creates and activates a new ServiceAgreement from a quote |
| `runPipeline(Quote, options)` | Full pipeline: approve → jobs → optional plan/agreement activation |

---

### New Events (`App\Events\Work\`)

| Event | Trigger |
|---|---|
| `FieldServiceSaleCreated` | Quote tagged as field-service eligible |
| `FieldServiceSaleApproved` | Quote accepted/approved |
| `FieldServiceSaleConvertedToJob` | Jobs created from accepted quote |
| `FieldServiceSaleConvertedToPlan` | ServicePlan created from accepted quote |
| `FieldServiceAgreementSaleCreated` | New ServiceAgreement created from a sale |
| `FieldServiceAgreementSaleActivated` | Existing agreement activated by a sale |
| `FieldServiceAgreementSaleExtended` | Agreement extended (renewed) by a sale |
| `SaleServiceCoverageApplied` | Coverage applied to agreement from a sale |

---

### New Listeners (`App\Listeners\Work\`)

| Listener | Event | Behaviour |
|---|---|---|
| `FieldServiceSaleApprovedListener` | `FieldServiceSaleApproved` | Calls `FieldServiceSaleService::convertQuoteToJobs()` |
| `FieldServiceAgreementSaleActivatedListener` | `FieldServiceAgreementSaleActivated` | Stub; extend for notifications/CRM sync |

---

## Domain Connectivity

| Domain | Status |
|---|---|
| CRM: Customer | ✅ Fully linked — soldServiceJobs, soldAgreements, saleToServiceTimeline |
| CRM: Enquiry/Deal | ⚡ Partial — FK columns exist, automated transition not yet driven |
| Finance: Quote/Invoice | ✅ Canonical — no duplicate billing layer |
| Service: ServiceJob | ✅ sale_line_id, originatingSale(), quoteItem() |
| Service: ServicePlan | ✅ originatingSale() via agreement chain |
| Agreements: ServiceAgreement | ✅ originating_quote_id, originatingSale(), saleCoverageSummary() |
| Equipment/Assets | ⚡ Partial — deferred to fieldservice_sale_agreement_equipment_stock |
| Premises | ✅ Quote.premises_id → ServiceJob.premises_id propagated on job creation |
| Scheduling/Dispatch | ✅ Compatible — sale-created jobs appear on surface automatically |
| Repair | ✅ Compatible — sale-originated jobs can generate repair orders via existing events |

---

## Next Modules

- `fieldservice_sale_agreement_equipment_stock` — sold equipment → InstalledEquipment, SiteAsset, EquipmentWarranty
- `fieldservice_sale_recurring` — recurring sale line → automatic ServicePlan generation

---

## Overlap Map

See `fieldservice_sale_overlap_map.json` at repository root.
