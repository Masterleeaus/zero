<?php
namespace Modules\PropertyManagement\Policies;

use Modules\PropertyManagement\Policies\Concerns\ChecksPmPermissions;

class PropertyPolicy
{
    use ChecksPmPermissions;

    public function viewAny($user): bool { return $this->has($user, 'propertymanagement.view'); }
    public function view($user): bool { return $this->has($user, 'propertymanagement.view'); }
    public function create($user): bool { return $this->has($user, 'propertymanagement.create'); }
    public function update($user): bool { return $this->has($user, 'propertymanagement.update'); }
    public function delete($user): bool { return $this->has($user, 'propertymanagement.delete'); }
}
