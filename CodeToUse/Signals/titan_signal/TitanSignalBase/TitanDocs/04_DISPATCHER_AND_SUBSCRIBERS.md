# Dispatcher and Subscribers

This pass adds the first routing spine after signal persistence.

## Dispatcher

`SignalDispatcher` reads pending rows from `tz_signal_queue`, dispatches each signal to registered subscribers, records dispatch audit entries, and marks queue rows as `dispatched`.

## Subscribers

### ZeroSubscriber
Builds envelope hints for Titan Zero prioritisation.

### PulseSubscriber
Marks whether a signal is automation-ready or blocked on approval.

### RewindSubscriber
Captures replay anchor metadata so signals can be tied back to process timelines.

## Current Limitation

Subscribers currently return structured receipts instead of invoking remote systems. That is intentional: this pass preserves governed routing without hard-coding downstream engines.
