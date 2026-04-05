# FINANCE SIGNAL INTEGRATION

## Overview
Finance Pass 4 emits 7 orchestration-ready events that feed Titan Zero's automation and decision layers.

## Events

| Event | Namespace | Trigger |
|---|---|---|
| `FinancialSnapshotUpdated` | `App\Events\Money` | Every snapshot() call |
| `ForecastGenerated` | `App\Events\Money` | Every forecast generation |
| `MarginDropDetected` | `App\Events\Money` | Margin below threshold |
| `MarginThresholdCrossed` | `App\Events\Money` | Margin goes negative |
| `CashRunwayWarning` | `App\Events\Money` | Cash buffer below minimum |
| `CashflowRiskDetected` | `App\Events\Money` | Projected outflow > inflow |
| `FinancialRiskDetected` | `App\Events\Money` | Any alert condition exists |

## Registration
All 7 events registered in `app/Providers/EventServiceProvider.php` under the Finance Pass 4 comment block.

## Payload Examples

### FinancialSnapshotUpdated
```php
new FinancialSnapshotUpdated(int $companyId, array $snapshot)
```

### ForecastGenerated
```php
new ForecastGenerated(int $companyId, array $forecast)
```

### MarginDropDetected
```php
new MarginDropDetected(int $companyId, array $detail)
// detail: {gross_margin_pct, threshold_pct}
```

### CashRunwayWarning
```php
new CashRunwayWarning(int $companyId, array $detail)
// detail: {cash_buffer_days, threshold_days}
```

## Integration Points
These signals feed:
- Titan Zero decision queue
- Dispatch logic
- Automation engine
- Orchestration layer

Listeners can be attached in `EventServiceProvider::$listen` against any event class.
