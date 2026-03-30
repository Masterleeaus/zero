<?php
namespace Modules\PropertyManagement\Policies;

use Modules\PropertyManagement\Policies\Concerns\ChecksPmPermissions;

class PropertyInspectionPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'propertymanagement.inspections.view'); }
    public function view($user): bool { return $this->has($user, 'propertymanagement.inspections.view'); }
    public function create($user): bool { return $this->has($user, 'propertymanagement.inspections.create'); }
    public function update($user): bool { return $this->has($user, 'propertymanagement.inspections.update'); }
    public function delete($user): bool { return $this->has($user, 'propertymanagement.inspections.delete'); }
}
