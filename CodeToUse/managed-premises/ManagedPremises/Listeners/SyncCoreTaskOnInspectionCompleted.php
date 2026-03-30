<?php
namespace Modules\ManagedPremises\Listeners;

use Modules\ManagedPremises\Events\PropertyInspectionCompleted;
use Modules\ManagedPremises\Integrations\Core\TaskAdapterInterface;

class SyncCoreTaskOnInspectionCompleted
{
    public function __construct(protected TaskAdapterInterface $tasks) {}

    public function handle(PropertyInspectionCompleted $event): void
    {
        // Optional: core task creation can be handled by the same adapter (future expansion).
        // For now we only expose the event hook.
    }
}
