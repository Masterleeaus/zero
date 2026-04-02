# DOC 57 — Copilot Prompt 07: Automation Rebinder

## Objective
Reconnect automation safely into the governed Nexus pipeline.

## Tasks
1. classify existing automation rules by mode
2. convert direct actions into draft/proposal actions where needed
3. ensure automation outputs re-enter ProcessRecord pipeline
4. prevent automation from bypassing Sentinel and AEGIS
5. output automation ownership and risk table

## Critical Rules
1. Automation may propose, queue, remind, enrich, or draft.
2. Automation may not silently finalize governed artifacts.
3. Existing useful automation logic must be preserved when possible.

## Required Outputs
1. automation_mode_map.md
2. automation_risk_table.md
3. direct_execution_remediation.md
