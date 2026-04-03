# Merge Validation Report

Date: 2026-03-31

## Phase 14 — Boot Verification
| Check | Status | Notes |
| --- | --- | --- |
| WorkCoreServiceProvider registered in `config/app.php` | ✅ Pass | Provider added to application providers. |
| `config('workcore.vertical')` returns `cleaning` | ✅ Pass | Default vertical set to `cleaning`. |
| `workcore_label('sites')` returns `Jobs` | ✅ Pass | Label resolver added with mapping for sites → Jobs. |
| `php artisan route:list --name=dashboard.crm` | ⚠️ Blocked | Not executed: composer install requires GitHub token (cannot complete dependency install to run artisan). Routes are defined under `routes/core/crm.routes.php`. |
| `php artisan schedule:list` includes `agreements:run-scheduled` | ⚠️ Blocked | Not executed: composer install blocked by GitHub token. Schedule configuration shows `agreements:run-scheduled` scheduled hourly in `app/Console/Kernel.php`. |

## Phase 15 — Full Validation Checklist
| Command | Status | Notes |
| --- | --- | --- |
| `composer install --ignore-platform-req=ext-redis` | ❌ Fail | Blocked by GitHub OAuth token prompt while fetching packages (private repo access required). |
| `php artisan migrate --dry-run` | ⚠️ Blocked | Not runnable because composer install failed (dependencies not present). |
| `php artisan migrate` | ⚠️ Blocked | Not runnable because composer install failed. |
| `php artisan db:seed --class=MenuSeeder` | ⚠️ Blocked | Not runnable because composer install failed. |
| `php artisan test --stop-on-failure` | ⚠️ Blocked | Not runnable because composer install failed. |
| `npm run build` | ⚠️ Blocked | Not run; PHP dependency install failed so build pipeline not attempted. |

## Manual Panel Checks
| Scenario | Status | Notes |
| --- | --- | --- |
| Create customer → enquiry → quote → invoice → payment | ⚠️ Not run | Application not booted due to missing dependencies (composer install blocked). |
| Create site → service job → checklist → attendance | ⚠️ Not run | Application not booted due to missing dependencies. |
| Insights overview shows real company data | ⚠️ Not run | Application not booted due to missing dependencies. |
| Support ticket lifecycle (open → waiting → resolved) | ⚠️ Not run | Application not booted due to missing dependencies. |
| Menu shows correct cleaning vertical labels | ⚠️ Not run | Application not booted due to missing dependencies. |
| Company A user cannot see Company B data | ⚠️ Not run | Application not booted due to missing dependencies. |
| All menu links resolve without 404 | ⚠️ Not run | Application not booted due to missing dependencies. |
