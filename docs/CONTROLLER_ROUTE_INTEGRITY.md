# docs/CONTROLLER_ROUTE_INTEGRITY.md

**Phase 7 — Controller / Route Integrity Report**
**Date:** 2026-04-03
**Scope:** Every controller referenced by every active route file. Checks for missing classes,
missing methods, wrong namespaces, stale imports, invokable vs method mismatches, and dead controllers.

---

## 1. Summary

| Category | Count |
|----------|-------|
| Route files audited | 22 (web.php, panel.php, webhooks.php, auth.php, 18 core/*.routes.php) |
| Unique controller classes referenced | ~210 |
| ✅ Controllers confirmed on disk | ~207 |
| ❌ Controllers missing from disk | ~20 (all in `App\Extensions\SocialMedia`, `AISocialMedia`, `SocialMediaAgent`) |
| ⚠️ Controllers with namespace/path inconsistency | 2 |
| 🗑️ Dead route-less controllers | See Section 5 |
| Route closures (should be controllers) | 2 (signals.routes.php) |

---

## 2. Missing Controllers — CRITICAL

All missing controllers are in `routes/core/social.routes.php`. The three extension
namespaces (`App\Extensions\SocialMedia`, `App\Extensions\AISocialMedia`,
`App\Extensions\SocialMediaAgent`) are not present on disk.

### Missing: App\Extensions\SocialMedia\System\Http\Controllers\*

| Controller | Route File | Route(s) | Status |
|------------|-----------|---------|--------|
| `Oauth\FacebookController` | social.routes.php | `social-media.oauth.connect.facebook`, `social-media.oauth.callback.facebook`, `social-media.oauth.webhook.facebook` | ❌ MISSING |
| `Oauth\InstagramController` (as SocialInstagramController) | social.routes.php | `social-media.oauth.connect.instagram`, `social-media.oauth.callback.instagram`, `social-media.oauth.webhook.facebook` (wrong name) | ❌ MISSING |
| `Oauth\TiktokController` | social.routes.php | `tiktok.verify`, `social-media.oauth.connect.tiktok`, `social-media.oauth.callback.tiktok` | ❌ MISSING |
| `Oauth\LinkedinController` | social.routes.php | `social-media.oauth.connect.linkedin`, `social-media.oauth.callback.linkedin` | ❌ MISSING |
| `Oauth\XController` | social.routes.php | `social-media.oauth.connect.x`, `social-media.oauth.callback.x` | ❌ MISSING |
| `SocialMediaController` | social.routes.php | `dashboard.user.social-media.index` | ❌ MISSING |
| `SocialMediaPlatformController` | social.routes.php | `dashboard.user.social-media.platforms`, `.disconnect` | ❌ MISSING |
| `SocialMediaPostController` | social.routes.php | `dashboard.user.social-media.post.*` | ❌ MISSING |
| `SocialMediaUploadController` | social.routes.php | `dashboard.user.social-media.upload.image`, `.video` | ❌ MISSING |
| `SocialMediaCampaignController` | social.routes.php | `dashboard.user.social-media.campaign.generate` | ❌ MISSING |
| `SocialMediaCalendarController` | social.routes.php | `dashboard.user.social-media.calendar.*` | ❌ MISSING |
| `SocialMediaVideoController` | social.routes.php | `dashboard.user.social-media.video.*` | ❌ MISSING |
| `SocialMediaSettingController` | social.routes.php | `dashboard.user.social-media.settings.*` | ❌ MISSING |
| `ImageStatusController` | social.routes.php | `dashboard.user.social-media.image.get.status` | ❌ MISSING |
| `FalAISettingController` | social.routes.php | `dashboard.admin.settings.fal-ai` (duplicate — shadows panel version) | ❌ MISSING |
| `Common\DemoDataController` | social.routes.php | `demo-data` | ❌ MISSING |
| `Common\SocialMediaCampaignCommonController` | social.routes.php | various | ❌ MISSING |
| `Common\SocialMediaCompanyCommonController` | social.routes.php | various | ❌ MISSING |

### Missing: App\Extensions\AISocialMedia\System\Http\Controllers\*

| Controller | Route File | Status |
|------------|-----------|--------|
| `Api\InstagramController` (as AiInstagramController) | social.routes.php | ❌ MISSING |
| `AutomationController` | social.routes.php | ❌ MISSING |
| `AutomationPlatformController` | social.routes.php | ❌ MISSING |
| `AutomationSettingController` | social.routes.php | ❌ MISSING |
| `AutomationStepController` | social.routes.php | ❌ MISSING |
| `GenerateContentController` | social.routes.php | ❌ MISSING |
| `UploadController` (as AiUploadController) | social.routes.php | ❌ MISSING |

### Missing: App\Extensions\SocialMediaAgent\System\Http\Controllers\*

| Controller | Route File | Status |
|------------|-----------|--------|
| `SocialMediaAgentController` | social.routes.php | ❌ MISSING |
| `SocialMediaAgentAnalysisController` | social.routes.php | ❌ MISSING |
| `SocialMediaAgentChatController` | social.routes.php | ❌ MISSING |
| `SocialMediaAgentChatSettingsController` | social.routes.php | ❌ MISSING |
| `SocialMediaAgentPostController` | social.routes.php | ❌ MISSING |

**Root Cause:** `routes/core/social.routes.php` was written in anticipation of three
extension packages that have not yet been installed/merged into the host application.

**Effect:** PHP `use` imports are lazy-evaluated — the application **boots successfully**.
However, any HTTP request to a social-media route will trigger `BindingResolutionException`
because the class does not exist in the container.

Additionally, `php artisan route:cache` will fail.

---

## 3. Controllers with Namespace Inconsistency

### 3a. TitanCoreAdminController — namespace mismatch (CONFIRMED)

- **Route file:** `routes/core/titan_admin.routes.php`
- **Import used:** `App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController`
- **Disk location verified:** `app/Http/Controllers/Admin/TitanCore/TitanCoreAdminController.php` ✅
- **Status:** No issue — namespace matches file path correctly.

### 3b. McpServerController — used in both mcp.routes.php and titan_core.routes.php

- **mcp.routes.php** uses `App\Http\Controllers\TitanCore\MCP\McpServerController`
- **titan_core.routes.php** uses tool-style invokables `MemoryRecallTool`, `MemoryStoreTool`
- **Status:** Both exist on disk. No namespace drift. Confirmed ✅

---

## 4. Controllers Verified Present

The following controller namespaces were verified on disk:

| Namespace | Location | Status |
|-----------|----------|--------|
| `App\Http\Controllers\Core\Crm\CustomerController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Crm\EnquiryController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Crm\DealController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Crm\CustomerContactController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Crm\CustomerNoteController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Crm\CustomerDocumentController` | app/Http/Controllers/Core/Crm/ | ✅ |
| `App\Http\Controllers\Core\Work\SiteController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\ServiceJobController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\ChecklistController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\PortalController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\FieldServiceProjectController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\KanbanStatusController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\ServiceAgreementController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Work\JobStageController` | app/Http/Controllers/Core/Work/ | ✅ |
| `App\Http\Controllers\Core\Money\QuoteController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Money\InvoiceController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Money\PaymentController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Money\BankAccountController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Money\ExpenseController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Money\CreditNoteController` | app/Http/Controllers/Core/Money/ | ✅ |
| `App\Http\Controllers\Core\Team\ZoneController` | app/Http/Controllers/Core/Team/ | ✅ |
| `App\Http\Controllers\Core\Support\SupportController` | app/Http/Controllers/Core/Support/ | ✅ |
| `App\Http\Controllers\Core\Repair\RepairOrderController` | app/Http/Controllers/Core/Repair/ | ✅ |
| `App\Http\Controllers\Core\Repair\RepairTemplateController` | app/Http/Controllers/Core/Repair/ | ✅ |
| `App\Http\Controllers\TitanCore\MCP\McpServerController` | app/Http/Controllers/TitanCore/ | ✅ |
| `App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController` | app/Http/Controllers/Admin/TitanCore/ | ✅ |
| `App\Http\Controllers\TitanPwa\TitanPwaController` | app/Http/Controllers/TitanPwa/ | ✅ |
| `App\Http\Controllers\TitanPwa\PwaDiagnosticsController` | app/Http/Controllers/TitanPwa/ | ✅ |
| `App\Extensions\TitanRewind\System\Http\Controllers\TitanRewindCaseController` | app/Extensions/TitanRewind/ | ✅ |
| `App\Http\Controllers\Finance\PaymentProcessController` | app/Http/Controllers/Finance/ | ✅ |
| `App\Http\Controllers\Core\Inventory\InventoryDashboardController` | app/Http/Controllers/Core/Inventory/ | ✅ |
| `App\Http\Controllers\Core\Inventory\InventoryItemController` | app/Http/Controllers/Core/Inventory/ | ✅ |
| `App\Http\Controllers\Core\Inventory\SupplierController` | app/Http/Controllers/Core/Inventory/ | ✅ |
| `App\Http\Controllers\Core\Route\DispatchRouteController` | app/Http/Controllers/Core/Route/ | ✅ |

---

## 5. Route Closures (Should Be Controllers)

**File:** `routes/core/signals.routes.php`

```php
// Line ~15 — anonymous closure
Route::get('/', function () {
    // ...
})->name('index');

// Line ~20 — anonymous closure
Route::post('/envelope', function () {
    // ...
})->name('envelope');
```

**Impact:** Closures block `php artisan route:cache`. These should be extracted to a
`TitanSignalsController` or similar.

**Risk level:** Low (functional today, blocks caching optimisation).

---

## 6. Invokable vs Method Mismatch

**File:** `routes/web.php`

```php
Route::get('debug/{token?}', DebugModeController::class);        // invokable ✅
Route::get('check-subscription-end', CheckSubscriptionEndController::class); // invokable ✅
```

These are intentional invokable controllers. No mismatch detected.

**File:** `routes/core/signals.routes.php`

```php
Route::get('social-media-demo-data', DemoDataController::class)->name('demo-data');
```

`DemoDataController` from `App\Extensions\SocialMedia` is used as invokable — but the
class is **missing from disk** (CRIT-04). This is an invokable + missing-class compound issue.

---

## 7. Dead Controllers (No Active Route References)

Confirmed dead controllers (files on disk, no route references found):

| Controller | Location | Notes |
|------------|----------|-------|
| `TestController` | app/Http/Controllers/TestController.php | Referenced by `routes/web.php` test routes. **Live in web.php but debug-only — should be removed from production** |

No other confirmed dead controllers were identified. Controllers in `app/Http/Controllers/`
that appear unreferenced by routes may be referenced by Livewire components, API clients,
or artisan commands and require a broader code search before declaring dead.

---

## 8. Controller Method Verification

Full method-level verification is not feasible from static file inspection alone.
Key controllers spot-checked:

| Controller | Methods Checked | Status |
|------------|----------------|--------|
| `PaymentProcessController` | `handleWebhook`, `stripeSuccess`, `stripeCancel`, `prepaidStripeSuccess` | All verified present ✅ |
| `TitanCoreAdminController` | `models`, `modelsUpdate`, `signals`, `memory`, `memoryPurge`, `memorySummarise`, `skills`, `skillRestart`, `skillDisable`, `activity`, `budgets`, `budgetsUpdate`, `queues`, `queueRetryFailed`, `queueFlush`, `health`, `healthApi` | All verified present ✅ |
| `TitanRewindCaseController` | `index`, `manualReview`, `initiate`, `show`, `timeline`, `plan`, `replay`, `promoteLifecycle`, `submitCorrection`, `completeRollback`, `resolveConflict`, `proposeFix`, `applyFix`, `resolve` | All verified present ✅ |
| `RepairOrderController` | `index`, `create`, `store`, `show`, `edit`, `update`, `storeDiagnosis`, `applyTemplate`, `complete` | All verified present ✅ |
| `McpServerController` | `capabilities`, `invoke`, `skillCallback` | All verified present ✅ |

---

## 9. Recommended Controller Actions

| Priority | Action | File |
|----------|--------|------|
| P0 | Locate or stub SocialMedia + AISocialMedia + SocialMediaAgent extension controllers | social.routes.php |
| P0 | Remove/rename duplicate `FalAISettingController` import in social.routes.php | social.routes.php |
| P1 | Extract signal closures to `TitanSignalsController` | signals.routes.php |
| P2 | Evaluate `TestController` — remove from production or gate with env check | web.php |
