<?php

namespace Modules\Inventory\Policies;

use App\Models\User;
use Modules\Inventory\Entities\StockMovement;

class StockMovementPolicy
{
    public function view(User $user, ?StockMovement $m = null): bool
    {
        return method_exists($user,'hasPermissionTo') ? $user->hasPermissionTo('inventory.view') : true;
    }
    public function manage(User $user, ?StockMovement $m = null): bool
    {
        return method_exists($user,'hasPermissionTo') ? $user->hasPermissionTo('inventory.manage') : false;
    }
}
