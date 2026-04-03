<?php
namespace Modules\PropertyManagement\Policies;

use Modules\PropertyManagement\Policies\Concerns\ChecksPmPermissions;

class PropertyServicePlanPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'propertymanagement.plans.view'); }
    public function view($user): bool { return $this->has($user, 'propertymanagement.plans.view'); }
    public function create($user): bool { return $this->has($user, 'propertymanagement.plans.create'); }
    public function update($user): bool { return $this->has($user, 'propertymanagement.plans.update'); }
    public function delete($user): bool { return $this->has($user, 'propertymanagement.plans.delete'); }
}
