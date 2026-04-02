# DOC 51 — Copilot Prompt 01: Full-Tree Mode Classifier

## Objective
Deep-scan the clean original extension tree and assign every relevant file to one primary Nexus mode:
- Jobs
- Comms
- Finance
- Admin
- Social
- Shared Infrastructure (only when truly cross-mode)

## Critical Rules
1. Do not edit code in this pass.
2. Do not rename anything in this pass.
3. Do not create replacement files.
4. This pass is classification only.
5. Every controller, route, view, service, language file, config file, signal, automation rule, and component must receive a mode ownership label.
6. Any ambiguous item must be marked HUMAN_DECISION_REQUIRED.

## Scan Targets
- app/
- routes/
- resources/views/
- lang/
- config/
- extension manifests / providers
- JS files if they affect workflow ownership

## Required Output
Produce:
1. mode_ownership_registry.md
2. folder_mode_map.md
3. ambiguous_items.md
4. shared_infrastructure_candidates.md

## Required Classification Format
For each item:
- path
- type
- proposed_mode
- confidence
- reason
- status: LOCKED / HUMAN_DECISION_REQUIRED

## Success Criteria
- full tree classified
- no silent omissions
- no renames performed
- no code mutations performed
