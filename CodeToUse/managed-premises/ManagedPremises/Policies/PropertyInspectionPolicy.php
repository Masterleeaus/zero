<?php
namespace Modules\ManagedPremises\Policies;

use Modules\ManagedPremises\Policies\Concerns\ChecksPmPermissions;

class PropertyInspectionPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'managedpremises.inspections.view'); }
    public function view($user): bool { return $this->has($user, 'managedpremises.inspections.view'); }
    public function create($user): bool { return $this->has($user, 'managedpremises.inspections.create'); }
    public function update($user): bool { return $this->has($user, 'managedpremises.inspections.update'); }
    public function delete($user): bool { return $this->has($user, 'managedpremises.inspections.delete'); }
}
