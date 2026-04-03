# DOC 55 — Copilot Prompt 05: View Path Transformer

## Objective
Transform Blade view paths into canonical Nexus mode paths while preserving UI structure.

## Target View Paths
- panel.user.nexus.jobs.*
- panel.user.nexus.comms.*
- panel.user.nexus.finance.*
- panel.user.nexus.admin.*
- panel.user.nexus.social.*

## Tasks
1. classify every view surface by mode
2. rename paths in place where appropriate
3. rewrite include/component/view() references
4. preserve layout structure, spacing, cards, and composition
5. delete duplicate scaffold trees created by failed prior conversion
6. fix malformed literal-path directories

## Critical Rules
1. Do not rebuild UI from scratch.
2. Preserve visual implementation behavior.
3. No old/new parallel trees unless documented as LEGACY_COMPAT_ONLY.
4. Output a full path audit.

## Required Outputs
1. view_path_mapping.md
2. duplicate_tree_removals.md
3. malformed_path_fixes.md
