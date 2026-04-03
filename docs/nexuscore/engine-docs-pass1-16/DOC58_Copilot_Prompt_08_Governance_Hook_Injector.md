# DOC 58 — Copilot Prompt 08: Governance Hook Injector

## Objective
Attach AEGIS governance checkpoints across all five modes.

## Tasks
1. identify approval gates
2. identify permission gates
3. identify compliance checkpoints
4. identify escalation triggers
5. identify audit-log injection points
6. attach hold/reject/escalate logic

## Critical Rules
1. AEGIS governs; it does not originate business artifacts.
2. Every governed path must log its reasoning.
3. Governance should preserve mode semantics while remaining cross-mode capable.

## Required Outputs
1. governance_checkpoint_map.md
2. escalation_paths.md
3. audit_hook_map.md
