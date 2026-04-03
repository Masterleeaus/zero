# Remaining host-duplicate strip in pass 8

Removed in this pass because MagicAI should own them:
- `public/` root files and uploads placeholder
- `storage/` runtime leftovers

Still intentionally retained for merge reference only:
- `config/*.php` feature integration hints
- `routes/web.php` route source for slicing
- `database/migrations/*` until renamed/imported

Next practical step: import the first domain slice into MagicAI, not more stripping.