<?php

namespace App\Extensions\TitanTrust\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Placeholder permissions seeder (idempotent).
 * If your platform uses a permissions table (e.g., permissions / role_has_permissions),
 * add TitanTrust capabilities here following the platform's pattern.
 *
 * HARD RULE: Do not modify core permission architecture; only insert/update rows.
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // No-op by default to avoid assumptions about your permissions schema.
        // Recommended capabilities (add if your platform supports it):
        // - titantrust.view
        // - titantrust.capture
        // - titantrust.review
        // - titantrust.override
    }
}
