<?php
namespace Modules\PropertyManagement\Policies;

use Modules\PropertyManagement\Policies\Concerns\ChecksPmPermissions;

class PropertyVisitPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'propertymanagement.visits.view'); }
    public function view($user): bool { return $this->has($user, 'propertymanagement.visits.view'); }
    public function create($user): bool { return $this->has($user, 'propertymanagement.visits.create'); }
    public function update($user): bool { return $this->has($user, 'propertymanagement.visits.update'); }
    public function delete($user): bool { return $this->has($user, 'propertymanagement.visits.delete'); }
}
