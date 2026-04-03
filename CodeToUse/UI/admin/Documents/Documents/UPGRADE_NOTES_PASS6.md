# Documents Module — Pass 6 (Doctor Command)

This pass adds a lightweight diagnostic command to validate the Documents module health without breaking Worksuite rendering.

## New command

- `php artisan documents:doctor`
- `php artisan documents:doctor --fix` (creates missing storage folders only)

## Checks performed

- Required Documents routes exist (`documents.index`, `documents.show`, `documents.templates.index`)
- Titan Zero/Heroes routes detected (warn only if missing)
- Templates installed (warn if `documents_templates` empty; recommends seeding)
- Storage folders present (optional safe create)
- Core DB tables exist (documents, documents_templates, document_folders, document_files)

No AI calls, no provider calls, no sidebar/menu edits.
