# Extension Extraction Matrix

## Objective
Create the final execution index that maps each original extension surface into Nexus target ownership.

## Required Columns
- source_extension
- source_path
- source_entity
- target_mode
- target_entity
- target_tables
- target_controllers
- target_routes
- target_views
- signal_touchpoints
- governance_touchpoints
- status

## Status Values
- PRESERVE
- REPURPOSE
- SPLIT
- MERGE
- RETIRE
- HUMAN_DECISION_REQUIRED

## Rule
No extension surface may be transformed without first appearing in this matrix.
