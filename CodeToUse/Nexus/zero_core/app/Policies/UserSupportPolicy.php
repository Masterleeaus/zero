<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserSupport;

class UserSupportPolicy
{
    public function view(User $user, UserSupport $support): bool
    {
        return $user->isAdmin() || $support->company_id === $user->company_id;
    }

    public function update(User $user, UserSupport $support): bool
    {
        return $this->view($user, $support);
    }
}
