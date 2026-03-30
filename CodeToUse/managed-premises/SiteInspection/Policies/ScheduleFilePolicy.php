<?php

namespace Modules\Inspection\Policies;

use App\Models\User;
use Modules\Inspection\Entities\ScheduleFile;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleFilePolicy
{{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {{
        return $user->can('siteinspection.view');
    }}

    public function view(User $user, ScheduleFile $model): bool
    {{
        return $user->can('siteinspection.view');
    }}

    public function create(User $user): bool
    {{
        return $user->can('siteinspection.create');
    }}

    public function update(User $user, ScheduleFile $model): bool
    {{
        return $user->can('siteinspection.update');
    }}

    public function delete(User $user, ScheduleFile $model): bool
    {{
        return $user->can('siteinspection.delete');
    }}
}
