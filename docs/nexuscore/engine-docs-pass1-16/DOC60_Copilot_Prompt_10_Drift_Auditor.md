# DOC 60 — Copilot Prompt 10: Drift Auditor

## Objective
Perform final drift audit after conversion.

## Tasks
1. detect duplicate trees
2. detect legacy orphan folders
3. detect unmapped routes/controllers/views/services
4. detect unlabeled signals
5. detect naming inconsistencies
6. detect bypassed governance paths
7. emit final remediation list

## Critical Rules
1. Do not hide unresolved drift.
2. Output exact paths and references.
3. Distinguish:
   - safe legacy compatibility
   - accidental duplication
   - unresolved semantic conflicts

## Required Outputs
1. drift_audit_report.md
2. orphaned_legacy_items.md
3. unresolved_semantic_conflicts.md
4. final_remediation_queue.md
