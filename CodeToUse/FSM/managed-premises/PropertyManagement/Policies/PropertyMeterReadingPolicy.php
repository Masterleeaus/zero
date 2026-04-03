<?php

namespace Modules\PropertyManagement\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\PropertyManagement\Entities\PropertyMeterReading;

class PropertyMeterReadingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->permission('propertymanagement.meters.view') !== 'none';
    }

    public function view(User $user, PropertyMeterReading $reading): bool
    {
        return $user->permission('propertymanagement.meters.view') !== 'none';
    }

    public function create(User $user): bool
    {
        return $user->permission('propertymanagement.meters.manage') !== 'none';
    }

    public function delete(User $user, PropertyMeterReading $reading): bool
    {
        return $user->permission('propertymanagement.meters.manage') !== 'none';
    }
}
