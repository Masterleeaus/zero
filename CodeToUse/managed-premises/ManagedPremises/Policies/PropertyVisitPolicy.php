<?php
namespace Modules\ManagedPremises\Policies;

use Modules\ManagedPremises\Policies\Concerns\ChecksPmPermissions;

class PropertyVisitPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'managedpremises.visits.view'); }
    public function view($user): bool { return $this->has($user, 'managedpremises.visits.view'); }
    public function create($user): bool { return $this->has($user, 'managedpremises.visits.create'); }
    public function update($user): bool { return $this->has($user, 'managedpremises.visits.update'); }
    public function delete($user): bool { return $this->has($user, 'managedpremises.visits.delete'); }
}
