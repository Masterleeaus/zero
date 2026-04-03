<?php

namespace Modules\TitanTalk\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use Modules\TitanTalk\Models\Entity;

class EntityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Entity $entity): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Entity $entity): bool { return true; }
    public function delete(User $user, Entity $entity): bool { return true; }
}
