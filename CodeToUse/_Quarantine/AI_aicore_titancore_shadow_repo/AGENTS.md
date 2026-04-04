# TitanZero Agent Rules

## Always do first
- Scan the current host codebase before edits.
- Scan any source folders fully before extraction or merge.
- Identify overlaps with CRM, Money, and Work domains before introducing new logic.

## Working style
- Preserve existing code.
- Prefer reuse, extension, adaptation, and refactor over replacement.
- Do not scaffold duplicates.
- Do not infer capability from names alone.

## Merge policy
- Merge original source logic first.
- Rename/restructure only in later dedicated passes.
- Strip duplicate infrastructure from imported code.
- Bridge shared entities into host systems.

## Required outputs
- clear file mapping
- conflict notes
- deferred rename notes
- validation summary
