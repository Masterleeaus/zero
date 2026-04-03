# DOC 52 — Copilot Prompt 02: Entity Mapping Lock

## Objective
Lock the universal grammar and entity overlay mappings after mode classification.

## Inputs
- mode_ownership_registry.md
- ambiguous_items.md
- existing legacy extension entities

## Tasks
1. extract all user-facing entities
2. extract all structural entities
3. map each to:
   - universal Nexus term
   - mode-specific visible term
   - structural grammar role:
     - Actor
     - Item
     - Container
     - Owner
     - Schedule
     - Event
     - Outcome

## Critical Rules
1. Do not rename files in this pass.
2. Do not rewrite routes in this pass.
3. This is a terminology lock pass only.
4. Ambiguous terms must remain open if confidence is low.

## Required Outputs
1. entity_lock_table.md
2. mode_terminology_lock.md
3. unresolved_terms.md

## Success Criteria
- every core entity mapped
- grammar roles locked
- unresolved terms isolated explicitly
