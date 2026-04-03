# ProcessRecord Backfill Strategy

## Objective
Retrofit governance history into legacy actions.

## Steps
1. detect executable lifecycle transitions
2. map actions to ProcessRecord states
3. attach approval metadata
4. persist execution timestamps

## Outputs
processrecord_backfill_map.md
approval_gap_report.md
