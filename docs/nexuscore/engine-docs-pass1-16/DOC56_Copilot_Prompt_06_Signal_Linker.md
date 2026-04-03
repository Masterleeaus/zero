# DOC 56 — Copilot Prompt 06: Signal Linker

## Objective
Attach all relevant actions to the Nexus ProcessRecord and signal lifecycle.

## Inputs
- mode ownership docs
- route/controller maps
- entity lock docs

## Tasks
1. identify lifecycle actions by mode
2. attach ProcessRecord creation
3. attach signal emission points
4. attach validation stage hooks
5. attach processing stage hooks
6. attach outcome and audit signals

## Critical Rules
1. Nothing executes directly without process recording.
2. Signal ownership inherits mode ownership.
3. Preserve existing working actions while instrumenting the pipeline.

## Required Outputs
1. signal_catalog.md
2. process_record_touchpoints.md
3. lifecycle_gap_report.md
