# Copilot Task: Phase 2 — Dependency & Config Integration

## Context
WorkCore merge Phase 2. The pre-merge package identified feature-specific config and dependency items that need to carry across into the MagicAI host. The overlay `config/workcore.php` and provider helper files were prepared in the WorkCore ZIP but never placed into the host.

## Your Task

### 1. Install the WorkCore config file
Create `config/workcore.php` with the following content (the feature config for WorkCore capabilities):

```php
<?php

return [
    // Default vertical determines surface vocabulary (see config/verticals.php)
    'vertical' => env('WORKCORE_VERTICAL', 'cleaning'),

    // Feature flags — disable domains not yet fully integrated
    'features' => [
        'crm'            => true,
        'work'           => true,
        'money'          => true,
        'team'           => true,
        'insights'       => true,
        'support'        => true,
        'knowledgebase'  => false,  // Playbooks — enable once merged
        'noticeboard'    => false,  // Notices — enable once merged
        'teamchat'       => false,  // Team Chat — enable once merged
        'credit_notes'   => false,  // Credit Notes — enable once merged
        'recurring'      => false,  // Recurring Invoices — enable once merged
        'bank_accounts'  => false,  // Bank Accounts — enable once merged
    ],

    // Scheduler
    'agreement_scheduler_frequency' => env('AGREEMENT_SCHEDULER_FREQUENCY', 'hourly'),
];
```

### 2. Install the verticals config
Create `config/verticals.php` — copy from `WorkCore.zip → magicai_overlay/config/verticals.php`.
This enables multi-vertical vocabulary switching (cleaning, property management, etc.)

### 3. Install the VerticalLanguageResolver service
Copy from `WorkCore.zip → magicai_overlay/app/Services/VerticalLanguageResolver.php`
to `app/Services/VerticalLanguageResolver.php`

### 4. Install the workcore_helpers file
Copy from `WorkCore.zip → magicai_overlay/app/Support/workcore_helpers.php`
to `app/Support/workcore_helpers.php`

Then register it in `composer.json` autoload files:
```json
"autoload": {
    "files": [
        "app/Support/workcore_helpers.php"
    ]
}
```

Run `composer dump-autoload` after.

### 5. Audit composer.json for WorkCore-specific packages
Check `WorkCore.zip → MAGICAI_PREMERGE/06_DEPENDENCY_AUDIT.md` for any packages that were in WorkCore but not in the host's `composer.json`. For each missing feature-specific package:
- Add to `composer.json` if it's feature-specific (not a host-owned package)
- Skip if already present or if it's a generic Laravel package the host already owns

### 6. Create output doc
Create `docs/DEPENDENCY_RESOLUTION_REPORT.md`:
- List each workcore-specific package found
- Status: already in host / added / skipped (reason)
- Status of config/helper file placements

## Constraints
- Do NOT change `composer.json` host-owned packages (Laravel framework, Sanctum, etc.)
- Run `php artisan config:cache` after adding config files
- Run `php artisan config:clear` first to flush stale cache
