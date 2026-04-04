# MODULE 07 — TitanPredict: Predictive Lifecycle Engine

**Status:** Installed  
**Installed:** 2026-04-04  
**Migration prefix:** `2026_04_04_900300`

---

## Overview

TitanPredict is an AI-powered predictive lifecycle engine that forecasts service demand, asset failure probability, SLA risk, and workforce capacity gaps before they become operational problems.

Every prediction includes:
- An **explanation trace** with contributing signals and weights
- A **confidence score** (DECIMAL 5,4: 0.0000–1.0000)
- A **recommended action**
- A **feedback loop** via `PredictionOutcome` records

---

## Architecture

```
TitanPredictService
  ├── PredictionSignalExtractorService  → extracts domain signals
  ├── PredictionModelService            → routes calls through AI driver abstraction
  └── TitanPredictController            → HTTP surface
```

All AI calls go through `AiCompletionService` → `AnthropicService` (or other configured engines). The module **never calls AI APIs directly**.

---

## Database Tables

### `predictions`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| company_id | bigint | Tenancy via BelongsToCompany |
| prediction_type | string | `asset_failure\|sla_breach\|demand_surge\|capacity_gap\|maintenance_overdue\|inspection_due` |
| subject_type | string nullable | Morph class name |
| subject_id | bigint nullable | Morph ID |
| confidence_score | decimal(5,4) | 0.0000–1.0000 |
| predicted_at | timestamp | AI-predicted occurrence time |
| generated_at | timestamp | When prediction was created |
| expires_at | timestamp | Auto-expiry |
| status | string | `active\|triggered\|expired\|dismissed` |
| recommended_action | text | AI-generated recommendation |
| explanation_trace | json | Array of signal contributions |
| model_provider | string | `anthropic` by default |
| model_id | string | Model identifier |
| dismissed_by | bigint FK users | |
| dismissed_at | timestamp | |

### `prediction_signals`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| prediction_id | bigint FK → predictions | Cascade delete |
| signal_type | string | e.g. `asset_age_years`, `condition_status` |
| signal_source_type | string nullable | Morph class |
| signal_source_id | bigint nullable | Morph ID |
| signal_value | json | Raw signal value |
| weight | decimal(5,4) | Signal influence (0–1) |
| recorded_at | timestamp | |

### `prediction_outcomes`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| prediction_id | bigint unique FK | |
| outcome_occurred | boolean | Did the prediction trigger? |
| outcome_at | timestamp | When it occurred |
| variance_hours | float | Prediction vs actual variance |
| feedback_notes | text | Operator notes |
| recorded_by | bigint FK users | |

### `prediction_schedules`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| company_id | bigint | Tenancy |
| prediction_type | string | |
| frequency_hours | smallint | Default 24 |
| last_run_at | timestamp | |
| next_run_at | timestamp | |
| is_active | boolean | |
| config | json | Provider/model overrides |

---

## Models

| Model | Location | Key Traits |
|-------|----------|-----------|
| `Prediction` | `app/Models/Predict/Prediction.php` | `BelongsToCompany`, `SoftDeletes`, morphic `subject()` |
| `PredictionSignal` | `app/Models/Predict/PredictionSignal.php` | `HasFactory` |
| `PredictionOutcome` | `app/Models/Predict/PredictionOutcome.php` | `HasFactory` |
| `PredictionSchedule` | `app/Models/Predict/PredictionSchedule.php` | `BelongsToCompany` |

---

## Services

### `TitanPredictService`
Primary orchestrator. Key methods:

| Method | Returns |
|--------|---------|
| `generateAssetFailurePrediction(SiteAsset)` | `Prediction` |
| `generateSLARiskPrediction(ServiceAgreement)` | `Prediction` |
| `generateDemandForecast(int $companyId, Carbon)` | `Collection<Prediction>` |
| `generateCapacityGapPrediction(int $companyId, Carbon)` | `Prediction` |
| `dismissPrediction(Prediction, User)` | `void` |
| `recordOutcome(Prediction, bool, ?Carbon)` | `PredictionOutcome` |
| `getActivePredictions(int $companyId, ?string $type)` | `Collection` |

### `PredictionSignalExtractorService`
Extracts domain signals from Eloquent models:

| Method | Signal Types Extracted |
|--------|----------------------|
| `extractAssetSignals(SiteAsset)` | `asset_age_years`, `condition_status`, `last_service_days_ago`, `maintenance_overdue`, `inspection_overdue`, `repairs_last_12_months`, `under_warranty` |
| `extractJobHistorySignals(int, string)` | `job_volume_last_30_days`, `demand_trend_ratio` |
| `extractSLASignals(ServiceAgreement)` | `open_job_count`, `avg_job_duration_hours`, `agreement_status`, `overdue_visits` |
| `extractCapacitySignals(int, Carbon)` | `scheduled_job_count`, `active_technician_count`, `forecast_day_of_week` |

### `PredictionModelService`
Routes AI calls through the Entity/Engine driver abstraction:

- Uses `AiCompletionService::complete()` → `AnthropicService` by default
- Parses structured JSON response from AI
- Falls back to heuristic weighted-average if AI call fails
- Returns `['confidence', 'predicted_at', 'explanation', 'action']`

---

## Events

| Event | Fires When |
|-------|-----------|
| `PredictionGenerated` | Every new prediction is persisted |
| `HighConfidencePrediction` | `confidence_score >= 0.85` |
| `PredictionTriggered` | Outcome recorded with `occurred = true` |
| `PredictionFeedbackRecorded` | Any outcome feedback saved |

---

## Listeners

| Listener | Handles |
|----------|---------|
| `NotifyOnHighConfidencePrediction` | `HighConfidencePrediction` — logs + notification hook |
| `UpdateAssetPredictionOnServiceEvent` | Any event with `assetServiceEvent.site_asset_id` |
| `UpdateSLAPredictionOnJobCompletion` | Any event with `job.agreement_id` |

---

## Console Command

```bash
php artisan predict:run-schedules
php artisan predict:run-schedules --company=5
php artisan predict:run-schedules --type=demand_surge
```

Processes all `prediction_schedules` where `is_active = true` and `next_run_at <= now()`.

---

## Routes

File: `routes/core/predict.routes.php`

| Route Name | Method | Path | Description |
|-----------|--------|------|-------------|
| `dashboard.predict.index` | GET | `/dashboard/predict` | Active predictions list |
| `dashboard.predict.asset` | POST | `/dashboard/predict/asset/{asset_id}` | Asset failure prediction |
| `dashboard.predict.agreement` | POST | `/dashboard/predict/agreement/{agreement_id}` | SLA risk prediction |
| `dashboard.predict.capacity` | POST | `/dashboard/predict/capacity` | Capacity gap prediction |
| `dashboard.predict.dismiss` | POST | `/dashboard/predict/{prediction_id}/dismiss` | Dismiss prediction |
| `dashboard.predict.feedback` | POST | `/dashboard/predict/{prediction_id}/feedback` | Record outcome |

---

## Confidence Score Logic

- `confidence_score` is DECIMAL(5,4): `0.0000` to `1.0000`
- Computed by AI model from signal context
- Fallback: weighted average of extracted signal weights
- High-confidence threshold: `>= 0.85` → fires `HighConfidencePrediction` immediately
- Below threshold: stored as `active` for batch review

---

## Explanation Trace Format

```json
[
  { "signal": "asset_age_years",    "value": 8,     "weight": 0.50, "contribution": "medium" },
  { "signal": "condition_status",   "value": "fair", "weight": 0.45, "contribution": "medium" },
  { "signal": "maintenance_overdue","value": true,   "weight": 0.75, "contribution": "high"   }
]
```

---

## References

- `app/Models/Predict/` — 4 models
- `app/Services/Predict/` — 3 services
- `app/Events/Predict/` — 4 events
- `app/Listeners/Predict/` — 3 listeners
- `app/Http/Controllers/Predict/TitanPredictController.php`
- `routes/core/predict.routes.php`
- `app/Console/Commands/RunPredictionSchedules.php`
- `database/migrations/2026_04_04_900300_create_titan_predict_tables.php`
- `tests/Unit/Services/Predict/TitanPredictServiceTest.php`
- `tests/Unit/Services/Predict/PredictionSignalExtractorServiceTest.php`
- `tests/Feature/Predict/TitanPredictControllerTest.php`
