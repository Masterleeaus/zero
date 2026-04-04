# Provider and Route Wiring Audit
**Generated:** 2026-04-04 | **Scope:** All service providers, route files, event/listener registrations

---

## 1. Service Providers Registered

### app/config/app.php — Registered Providers

The following providers are in `app/Providers/` and are presumed registered in `config/app.php`:

| Provider | Status | Purpose |
|----------|--------|---------|
| AppServiceProvider | ACTIVE | Core boot, observers, Spatie Health |
| AuthServiceProvider | ACTIVE | Policy registration |
| BroadcastServiceProvider | ACTIVE | Broadcasting channels |
| EventServiceProvider | ACTIVE | Event/listener wiring (discovery disabled) |
| RouteServiceProvider | ACTIVE | Route loading, model bindings |
| AdminServiceProvider | ACTIVE | Admin module bindings |
| AwsServiceProvider | ACTIVE | AWS/S3 bindings |
| ChatbotServiceProvider | ACTIVE | Chatbot service bindings |
| ExtensionServiceProvider | ACTIVE | Extension install/management |
| MacrosServiceProvider | ACTIVE | Blade/HTML macros |
| TelescopeServiceProvider | ACTIVE | Debug telescope |
| TitanCoreServiceProvider | ACTIVE | TitanCore AI bindings |
| TitanPwaServiceProvider | ACTIVE | PWA system bindings |
| TitanSignalsServiceProvider | ACTIVE | Signal dispatcher bindings |
| ViewServiceProvider | ACTIVE | View composers, shared data |
| WorkCoreServiceProvider | ACTIVE (unverified) | WorkCore bindings — scope unclear |

### Missing Dedicated Providers (features without dedicated providers):
- No dedicated FinanceServiceProvider (finance routes/services loaded via core routing)
- No dedicated DispatchServiceProvider
- No dedicated FsmServiceProvider
- No dedicated RepairServiceProvider
- No dedicated SecurityServiceProvider

**Assessment:** Feature providers are not used; all features share the single EventServiceProvider and RouteServiceProvider approach.

---

## 2. Route Loading Architecture

### RouteServiceProvider::loadCoreRoutes()

**Method:** Glob `routes/core/*.routes.php`, filter by lowercase regex `[a-z][a-z_]*\.routes\.php`, sort, require each.

**All routes loaded automatically** — no manual route registration required per new module.

**Routes under auth+throttle middleware (auth required):**
All routes in `routes/core/` are wrapped with `['auth', 'throttle:120,1']`

**Web routes (no auth):**
`routes/web.php`, `routes/auth.php`, `routes/webhooks.php`

**API routes (api middleware):**
`routes/api.php`

### Currently Active Core Route Files

| File | Domain | Status |
|------|--------|--------|
| admin.routes.php | Admin | ACTIVE |
| chat.routes.php | TitanChat | ACTIVE |
| crm.routes.php | CRM | ACTIVE |
| docs.routes.php | DocsExecution | ACTIVE |
| finance.routes.php | Finance (Titan pass) | ACTIVE |
| insights.routes.php | Insights | ACTIVE — controller unclear |
| inventory.routes.php | Inventory | ACTIVE |
| mcp.routes.php | MCP handlers | ACTIVE — controller wiring unclear |
| money.routes.php | Money/Finance | ACTIVE |
| portal.routes.php | Customer Portal | ACTIVE |
| predict.routes.php | TitanPredict | ACTIVE |
| project.routes.php | FieldService Projects | ACTIVE |
| pwa.routes.php | TitanPWA | ACTIVE |
| repair.routes.php | Repair | ACTIVE |
| rewind.routes.php | TitanRewind | ACTIVE |
| route.routes.php | Route Planning | ACTIVE |
| security.routes.php | Security | ACTIVE |
| signals.routes.php | TitanSignals | ACTIVE |
| social.routes.php | Social | ACTIVE |
| support.routes.php | Support | ACTIVE |
| team.routes.php | Team/Capabilities | ACTIVE |
| timegraph.routes.php | TimeGraph | ACTIVE |
| titan_admin.routes.php | Titan Admin | ACTIVE |
| titan_core.routes.php | TitanCore API | ACTIVE |
| trust.routes.php | TrustLedger | ACTIVE |
| work.routes.php | Work/Jobs | ACTIVE |

### Routes NOT Present (but domain exists):
| Missing Route File | Domain | Risk |
|-------------------|--------|------|
| mesh.routes.php | TitanMesh | CRITICAL — module marked installed but routes absent |

---

## 3. Event / Listener Registration Audit

### EventServiceProvider Summary

- `shouldDiscoverEvents()` returns `false` — **all events must be registered explicitly**
- 165+ Event use imports in file
- Estimated 100+ listener registrations in `$listen` array

### Event Families Confirmed in $listen

| Event Namespace | Events Registered | Confidence |
|----------------|-----------------|------------|
| Payment/webhook events | BankTransfer, Free, Invoice, Stripe, PayPal, etc. | High |
| Work core events | JobAssigned, JobCompleted, JobStageChanged, etc. | High |
| Work kanban/blocker | JobKanbanStateChanged, JobBlockerAdded, JobBlockerCleared | High |
| Equipment events | EquipmentInstalled/Removed/Replaced, Warranty* | High |
| Inspection events | 6 Inspection namespace events | High |
| Work Inspection events | WorkInspectionCompleted, WorkInspectionFailed | High |
| Premises events | HazardDetected, HazardResolved | High |
| Route events | RouteAssigned, RouteCreated, RouteStop* | High |
| CRM warranty events | 4 CrmWarranty* events | High |
| Trust events | LedgerEntryRecorded, ChainSealed, ChainTamperingDetected | High |
| Sync events | EdgeBatchSynced, EdgeConflict*, EdgeSyncFailed | High |
| TimeGraph events | 4 ExecutionTimeGraph events | High |
| Finance events | 4 JobCost/Finance events | High |
| Docs events | 4 DocsExecution events | High |
| Predict events | 4 TitanPredict events | High |
| Team capability events | 4 Team events | High |
| Repair events | RepairOrderCreated, RepairOrderCompleted, RepairOrderCancelled, + 14 more | High |
| Portal events | 6 Portal* events | High |
| Field service project events | 5 FieldServiceProject* events | High |
| Money events | ExpenseApproved, PaymentRecorded, SupplierBill*, etc. | High |

### Events Present in Code But Registration Uncertain

| Event | Location | Status |
|-------|----------|--------|
| RepairRescheduled | app/Events/Repair/ | UNVERIFIED — check $listen |
| RepairScheduled | app/Events/Repair/ | UNVERIFIED |
| RepairDispatchAssigned | app/Events/Repair/ | UNVERIFIED |
| RepairInvoiceGenerated | app/Events/Repair/ | UNVERIFIED |
| RepairFollowupScheduled | app/Events/Repair/ | UNVERIFIED |
| CrmFollowUpActivityCreated | app/Events/Crm/ | UNVERIFIED |
| CrmPipelineStageUpdatedFromService | app/Events/Crm/ | UNVERIFIED |
| CrmRepairDetected | app/Events/Repair/ | UNVERIFIED |
| CrmAgreementCandidate | app/Events/Repair/ | UNVERIFIED |
| CrmRecurringCandidate | app/Events/Repair/ | UNVERIFIED |
| CrmReplacementCandidate | app/Events/Repair/ | UNVERIFIED |
| CrmServiceUpgradeCandidate | app/Events/Repair/ | UNVERIFIED |
| ServiceJobCreatedFromDeal | app/Events/Crm/ | UNVERIFIED |
| ServiceJobCreatedFromEnquiry | app/Events/Crm/ | UNVERIFIED |
| ServiceJobClosedUpdatesCrm | app/Events/Crm/ | UNVERIFIED |
| VehicleAssignedToJob | app/Events/Work/ | UNVERIFIED |
| VehicleEquipmentMissing | app/Events/Work/ | UNVERIFIED |
| VehicleLocationUpdated | app/Events/Work/ | UNVERIFIED |
| VehicleRouteReady | app/Events/Work/ | UNVERIFIED |
| VehicleStockConsumed | app/Events/Work/ | UNVERIFIED |
| VehicleStockReserved | app/Events/Work/ | UNVERIFIED |
| RecurringPlanGenerated | app/Events/Work/ | UNVERIFIED |
| RecurringPlanUpdated | app/Events/Work/ | UNVERIFIED |
| RecurringSaleCreated | app/Events/Work/ | UNVERIFIED |
| RecurringVisitMaterialized | app/Events/Work/ | UNVERIFIED |
| RecurringEquipmentServiceCreated | app/Events/Work/ | UNVERIFIED |
| TimesheetApproved/Rejected/Submitted | app/Events/Work/ | UNVERIFIED |
| Security/LoginLockoutEvent | app/Events/Security/ | UNVERIFIED |
| TitanCore/TitanActivityEvent | app/Events/TitanCore/ | UNVERIFIED |

---

## 4. AppServiceProvider Boot Wiring

### Observer Registrations (confirmed in AppServiceProvider):
- AdObserver → Ad
- FeaturesMarqueeObserver → FeaturesMarquee
- BannerBottomTextObserver → BannerBottomText
- FrontendSectionsStatusObserver → FrontendSectionsStatus
- FrontendSettingObserver → FrontendSetting
- OpenAIGeneratorObserver → OpenAIGenerator
- SettingObserver → Setting
- SettingTwoObserver → SettingTwo
- UserObserver → User

### Finance Observers (app/Observers/Money/):
- Registered but specific models observed needs confirmation

### Missing Observer Registrations for New Domains:
- No confirmed observer for ServiceJob
- No confirmed observer for ServiceAgreement
- No confirmed observer for Invoice
- No confirmed observer for DispatchQueue

---

## 5. Menu / Extension Wiring

### Menu Service
- `app/Services/Common/MenuService.php` — exists
- `app/Services/Common/FrontMenuService.php` — exists
- `app/Models/Common/Menu.php`, `MenuGroup.php` — exist
- Extension menu installation via `ExtensionService` (`InstallExtension`/`UninstallExtension` traits)

### TitanRewind Extension
- `app/Extensions/TitanRewind/` — present with resources/
- routes/core/rewind.routes.php — loaded automatically

### Extensions Model
- `app/Models/Extension.php` — extension registry model
- `app/Models/Extensions/Introduction.php` — extension intro

### Menu Installer Gaps:
- Core work/FSM modules do NOT appear to have menu installer files
- No SQL-first install scripts found for new domains
- Module menu items likely added via manual service provider or seeder

---

## 6. Route Naming Conflicts (Potential)

Based on route file structure, the following naming overlap risks exist:

| Risk | Detail |
|------|--------|
| admin.* vs titan_admin.* | Two admin route files with potentially overlapping prefixes |
| finance.* vs money.* | Two finance-adjacent route files |
| docs.* | Could conflict with documentation browsing routes if any |

---

## 7. Summary Findings

| Category | Count | Critical Issues |
|----------|-------|----------------|
| Service providers | 16 active | WorkCoreServiceProvider scope unclear |
| Core route files | 26 auto-loaded | mesh.routes.php missing |
| Event families registered | 20+ | 25+ events with uncertain registration |
| Observer registrations | 9 confirmed | New domain observers not confirmed |
| Menu installers | Partial | No SQL-first install for new modules |
| Extension wiring | 1 extension (TitanRewind) | Others not installed as extensions |
