<?php

namespace Modules\TitanTalk\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use Modules\TitanTalk\Models\Intent;

class IntentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Intent $intent): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Intent $intent): bool { return true; }
    public function delete(User $user, Intent $intent): bool { return true; }
}
