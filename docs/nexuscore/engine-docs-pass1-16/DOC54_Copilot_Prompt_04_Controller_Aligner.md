# DOC 54 — Copilot Prompt 04: Controller Aligner

## Objective
Align controllers to mode ownership and Sentinel authority.

## Inputs
- route_mapping_table.md
- mode_ownership_registry.md

## Tasks
1. classify controller ownership
2. rename controllers only where necessary
3. update namespaces/imports/references
4. align controller responsibilities to one primary mode
5. identify mixed controllers requiring split/refactor
6. output a controller ownership matrix

## Critical Rules
1. Prefer in-place modification over scaffolding.
2. Do not create thin duplicate controllers.
3. Preserve current logic while cleaning semantic ownership.
4. Mark mixed controllers for staged split if needed.

## Required Outputs
1. controller_ownership_matrix.md
2. mixed_controller_report.md
3. controller_rename_map.md
