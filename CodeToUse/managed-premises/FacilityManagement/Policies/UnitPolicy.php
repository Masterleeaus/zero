<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Unit;
use App\Models\User;

class UnitPolicy {
  public function view(?User $user, Unit $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Unit $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Unit $model) { return $user->can('facilities.manage'); }
}