<?php

namespace Modules\Workflow\Services;

use Illuminate\Support\Str;

class TriggerRegistry
{
    /**
     * Returns trigger definitions used by the Workflow UI.
     * We support wildcard matching using Str::is().
     */
    public static function all(): array
    {
        return [
            // Core Eloquent lifecycle (system-wide)
            ['key' => 'eloquent.created:*', 'label' => 'Any record created (system-wide)', 'event' => 'eloquent.created: *'],
            ['key' => 'eloquent.updated:*', 'label' => 'Any record updated (system-wide)', 'event' => 'eloquent.updated: *'],
            ['key' => 'eloquent.deleted:*', 'label' => 'Any record deleted (system-wide)', 'event' => 'eloquent.deleted: *'],

            // Common domain events (modules may dispatch these)
            ['key' => 'customerconnect.message.sent', 'label' => 'CustomerConnect message sent', 'event' => 'customerconnect.message.sent'],
            ['key' => 'documents.created', 'label' => 'Document created', 'event' => 'documents.created'],
            ['key' => 'inspection.completed', 'label' => 'Inspection completed', 'event' => 'inspection.completed'],
            ['key' => 'assetmanager.maintenance.due', 'label' => 'Asset maintenance due', 'event' => 'assetmanager.maintenance.due'],
        ];
    }

    public static function matches(string $workflowTriggerEvent, string $incomingEventName): bool
    {
        if (!$workflowTriggerEvent) return false;

        // Allow exact match or wildcard match
        if ($workflowTriggerEvent === $incomingEventName) return true;

        return Str::is($workflowTriggerEvent, $incomingEventName);
    }
}
