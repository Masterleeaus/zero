# Finance — Labor Costing Bridge

**Date:** 2026-04-04  
**Service:** `App\Services\TitanMoney\LaborCostingService`

---

## Purpose

`LaborCostingService` calculates labor costs by combining `TimesheetSubmission.total_hours` with `StaffProfile.hourly_rate`. It can return cost arrays for analysis or create `JobCostAllocation` records against a job.

---

## How Labor Cost Is Calculated

```
cost = total_hours × hourly_rate
```

`hourly_rate` is sourced from `StaffProfile` where `user_id = submission->user_id`.  
If no `StaffProfile` exists for the user, `hourly_rate` defaults to `0.00`.

---

## Methods

### `costForTimesheetSubmission(TimesheetSubmission $submission): array`

Returns cost breakdown for a single submission without writing to DB.

```php
$result = $service->costForTimesheetSubmission($submission);
// ['hours' => 8.0, 'rate' => 25.00, 'cost' => 200.00, 'user_id' => 5]
```

---

### `costForUser(User $user, Carbon $weekStart, Carbon $weekEnd): array`

Aggregates all approved submissions for a user within a date range.

```php
$result = $service->costForUser($user, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-07'));
// ['user_id' => 5, 'hours' => 40.0, 'rate' => 25.00, 'cost' => 1000.00, 'submissions' => 5]
```

---

### `costForTeam(int $teamId, Carbon $from, Carbon $to): array`

Returns per-user cost rows for all team members with approved submissions in the period.

---

### `costForJob(ServiceJob $job): float`

Returns total labour-type cost allocated against a job.

```php
$total = $service->costForJob($job); // e.g. 1600.00
```

---

### `allocateTimesheetToJob(TimesheetSubmission $submission, ServiceJob $job): JobCostAllocation`

Looks up the user's `StaffProfile`, calculates cost, and writes a `JobCostAllocation` record.

```php
$allocation = $service->allocateTimesheetToJob($submission, $job);
// Creates: source_type=timesheet, cost_type=labour, amount=200.00
```

Internally delegates to `JobCostingService::allocateTimesheetLabor()`.

---

## Integration Points

| Source | Field |
|--------|-------|
| `TimesheetSubmission` | `total_hours`, `user_id`, `week_start`, `company_id` |
| `StaffProfile` | `hourly_rate` (looked up by `user_id`) |
| `JobCostAllocation` | Written with `source_type='timesheet'`, `cost_type='labour'` |
