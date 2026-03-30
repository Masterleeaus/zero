<?php

namespace Modules\Inspection\Policies;

use App\Models\User;
use Modules\Inspection\Entities\Schedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {{
        return $user->can('siteinspection.view');
    }}

    public function view(User $user, Schedule $model): bool
    {{
        return $user->can('siteinspection.view');
    }}

    public function create(User $user): bool
    {{
        return $user->can('siteinspection.create');
    }}

    public function update(User $user, Schedule $model): bool
    {{
        return $user->can('siteinspection.update');
    }}

    public function delete(User $user, Schedule $model): bool
    {{
        return $user->can('siteinspection.delete');
    }}
}
