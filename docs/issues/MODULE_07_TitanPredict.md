# MODULE 07 — TitanPredict: Predictive Lifecycle Engine

**Label:** `titan-module` `predictive` `ai` `forecasting` `maintenance`
**Milestone:** TitanZero Capability Expansion Pack v1
**Priority:** Medium-High

---

## Overview

Build the **TitanPredict** engine — an AI-powered predictive lifecycle system that forecasts service demand, asset failure probability, SLA risk, and workforce capacity gaps before they become problems. TitanPredict consumes data from the ExecutionTimeGraph (Module 06), CapabilityRegistry (Module 02), TitanContracts (Module 04), and site asset records to generate actionable predictions with confidence scores.

Predictions are not black boxes: every prediction includes an explanation trace, confidence level, contributing signals, and a recommended action. TitanPredict integrates with the existing Entity/Engine AI driver abstraction (`app/Domains/Entity/Drivers/`) for model calls.

---

## Pre-Scan Requirement (MANDATORY — run before any implementation pass)

1. Read `app/Domains/Entity/Drivers/` — ALL 214 driver files across all providers; understand the driver interface pattern
2. Read `app/Models/Facility/SiteAsset.php` and `AssetServiceEvent.php` — asset lifecycle data
3. Read `app/Models/Work/ServicePlan.php` — recurring demand data
4. Read `app/Models/Inspection/InspectionSchedule.php` — inspection frequency data
5. Read `app/Services/TimeGraph/ExecutionTimeGraphService.php` (Module 06 output) — time graph data source
6. Read `app/Services/Team/CapabilityRegistryService.php` (Module 02 output) — workforce data source
7. Read `app/Services/Work/ContractSLAService.php` (Module 04 output) — SLA risk data
8. Read `docs/nexuscore/` — scan for predictive analytics, forecasting, or AI decision docs
9. Read `docs/titancore/` — scan for Entity/Engine driver usage docs
10. Read `CodeToUse/` — scan ALL subdirectories for any prediction, forecasting, or analytics entity files

---

## Canonical Models to Extend / Reference

- `app/Models/Facility/SiteAsset.php` — primary asset lifecycle prediction target
- `app/Models/Work/ServicePlan.php` — demand forecasting source
- `app/Models/Work/ServiceJob.php` — historical execution data
- `app/Domains/Entity/Drivers/` — AI model call interface

---

## Required Output Artifacts

### Migrations
- `database/migrations/YYYY_MM_DD_create_titan_predict_tables.php`
  - `predictions` — core prediction store: `id`, `company_id`, `prediction_type` (asset_failure|sla_breach|demand_surge|capacity_gap|maintenance_overdue|inspection_due), `subject_type`, `subject_id`, `confidence_score` (decimal 5,4 — 0.0000 to 1.0000), `predicted_at` (datetime — when the event is predicted to occur), `generated_at`, `expires_at`, `status` (active|triggered|expired|dismissed), `recommended_action` (text), `explanation_trace` (json — contributing signals with weights), `model_provider` (string — which AI driver was used), `model_id` (string), `dismissed_by` (nullable user_id), `dismissed_at`
  - `prediction_signals` — input signals that contributed to a prediction: `prediction_id`, `signal_type`, `signal_source_type`, `signal_source_id`, `signal_value` (json), `weight` (decimal 5,4), `recorded_at`
  - `prediction_outcomes` — actual outcomes for model feedback: `prediction_id`, `outcome_occurred` (bool), `outcome_at` (datetime nullable), `variance_hours` (nullable — how far off the prediction was), `feedback_notes` (text nullable), `recorded_by`
  - `prediction_schedules` — recurring prediction runs: `company_id`, `prediction_type`, `frequency_hours`, `last_run_at`, `next_run_at`, `is_active`, `config` (json — prediction-type-specific params)
- Use `Schema::hasTable()` / `Schema::hasColumn()` guards

### Models
- `app/Models/Predict/Prediction.php` — with `BelongsToCompany`, morphic `subject()`, status scopes
- `app/Models/Predict/PredictionSignal.php` — contributing signal detail
- `app/Models/Predict/PredictionOutcome.php` — actual outcome feedback
- `app/Models/Predict/PredictionSchedule.php` — recurring run config

### Services
- `app/Services/Predict/TitanPredictService.php`
  - `generateAssetFailurePrediction(SiteAsset $asset): Prediction`
  - `generateSLARiskPrediction(ServiceAgreement $agreement): Prediction`
  - `generateDemandForecast(int $companyId, Carbon $forPeriod): Collection`
  - `generateCapacityGapPrediction(int $companyId, Carbon $forDate): Prediction`
  - `generateMaintenanceOverduePrediction(SiteAsset $asset): ?Prediction`
  - `dismissPrediction(Prediction $prediction, User $user): void`
  - `recordOutcome(Prediction $prediction, bool $occurred, ?Carbon $at = null): PredictionOutcome`
  - `getActivePredictions(int $companyId, string $type = null): Collection`
- `app/Services/Predict/PredictionSignalExtractorService.php`
  - `extractAssetSignals(SiteAsset $asset): array` — age, condition, service history, inspection scores
  - `extractJobHistorySignals(int $companyId, string $serviceType): array` — volume trends
  - `extractSLASignals(ServiceAgreement $agreement): array` — breach history, job complexity
  - `extractCapacitySignals(int $companyId, Carbon $date): array` — skill availability vs demand
- `app/Services/Predict/PredictionModelService.php`
  - `callPredictionModel(string $predictionType, array $signals, string $provider = 'anthropic'): array`
  - Uses Entity driver abstraction — calls appropriate driver from `app/Domains/Entity/Drivers/`
  - Returns: `['confidence' => float, 'predicted_at' => Carbon, 'explanation' => array, 'action' => string]`
- Console command: `app/Console/Commands/RunPredictionSchedules.php` — processes due `prediction_schedules`

### Events
- `app/Events/Predict/PredictionGenerated.php`
- `app/Events/Predict/HighConfidencePrediction.php` — fires when confidence > 0.85
- `app/Events/Predict/PredictionTriggered.php` — fires when predicted event actually occurs
- `app/Events/Predict/PredictionFeedbackRecorded.php`

### Listeners
- `app/Listeners/Predict/NotifyOnHighConfidencePrediction.php`
- `app/Listeners/Predict/UpdateAssetPredictionOnServiceEvent.php` — fires on `AssetServiceEvent` creation
- `app/Listeners/Predict/UpdateSLAPredictionOnJobCompletion.php`

### Signals
- Emit via `SignalDispatcher`: `predict.generated`, `predict.high_confidence`, `predict.triggered`, `predict.feedback_received`
- Include `prediction_type`, `subject_type`, `subject_id`, `confidence_score` in signal context

### Controllers / Routes
- `app/Http/Controllers/Predict/TitanPredictController.php`
  - `index(Request $request)` — active predictions dashboard for company
  - `asset(SiteAsset $asset)` — predictions for a specific asset
  - `agreement(ServiceAgreement $agreement)` — SLA risk predictions
  - `capacity(Request $request)` — workforce capacity forecast
  - `dismiss(Prediction $prediction)`
  - `feedback(Request $request, Prediction $prediction)` — record outcome
- Register in `routes/core/` as new `predict.php` route file

### Tests
- `tests/Unit/Services/Predict/TitanPredictServiceTest.php`
- `tests/Unit/Services/Predict/PredictionSignalExtractorServiceTest.php`
- `tests/Feature/Predict/TitanPredictControllerTest.php`

### Docs Report
- `docs/modules/MODULE_07_TitanPredict_report.md` — prediction type catalogue, signal extraction methodology, AI driver usage, feedback loop design, confidence threshold policy

### FSM Update
- Update `fsm_module_status.json` — set `titan_predict` to `installed`

---

## Architecture Notes

- All AI model calls MUST go through the Entity driver abstraction (`app/Domains/Entity/Drivers/`) — never call AI APIs directly
- Default provider: `anthropic` — but `prediction_schedules.config` can override per prediction type
- `confidence_score` is a 4-decimal float (0.0000–1.0000) — store as DECIMAL(5,4) for precision
- High-confidence threshold (> 0.85) triggers immediate notification — lower confidence predictions are batched
- `explanation_trace` JSON structure: `[{ "signal": "last_service_days_ago", "value": 187, "weight": 0.34, "contribution": "high" }, ...]`
- Feedback loop: `PredictionOutcome` records feed back into signal weight calibration — implement a weekly recalibration command
- Asset failure predictions use: condition_status, last_serviced_at, maintenance_interval_days, inspection_interval_days, historical service event types
- SLA risk predictions use: breach history, average job duration for service type, current open jobs count, technician capacity
- Must respect `company_id` scoping — predictions are company-scoped, never cross-tenant

---

## References

- `app/Domains/Entity/Drivers/` (all 214 drivers — understand interface pattern)
- `app/Models/Facility/SiteAsset.php`
- `app/Models/Facility/AssetServiceEvent.php`
- `app/Models/Work/ServicePlan.php`
- `app/Models/Work/ServiceJob.php`
- `app/Services/TimeGraph/ExecutionTimeGraphService.php` (Module 06)
- `app/Services/Team/CapabilityRegistryService.php` (Module 02)
- `app/Services/Work/ContractSLAService.php` (Module 04)
- `app/Titan/Signals/SignalDispatcher.php`
- `docs/nexuscore/` (predictive analytics, AI decision docs)
- `docs/titancore/` (Entity/Engine driver architecture)
- `CodeToUse/` (full scan for analytics/prediction entities)
