<?php

namespace Modules\Inventory\Policies;

use App\Models\User;
use Modules\Inventory\Entities\Item;

class ItemPolicy
{
    public function view(User $user, ?Item $item = null): bool
    {
        return method_exists($user,'hasPermissionTo') ? $user->hasPermissionTo('inventory.view') : true;
    }
    public function manage(User $user, ?Item $item = null): bool
    {
        return method_exists($user,'hasPermissionTo') ? $user->hasPermissionTo('inventory.manage') : false;
    }
}
