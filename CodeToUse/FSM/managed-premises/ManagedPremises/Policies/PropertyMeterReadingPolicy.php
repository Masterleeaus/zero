<?php

namespace Modules\ManagedPremises\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ManagedPremises\Entities\PropertyMeterReading;

class PropertyMeterReadingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->permission('managedpremises.meters.view') !== 'none';
    }

    public function view(User $user, PropertyMeterReading $reading): bool
    {
        return $user->permission('managedpremises.meters.view') !== 'none';
    }

    public function create(User $user): bool
    {
        return $user->permission('managedpremises.meters.manage') !== 'none';
    }

    public function delete(User $user, PropertyMeterReading $reading): bool
    {
        return $user->permission('managedpremises.meters.manage') !== 'none';
    }
}
