<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\UnitType;
use App\Models\User;

class UnitTypePolicy {
  public function view(?User $user, UnitType $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, UnitType $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, UnitType $model) { return $user->can('facilities.manage'); }
}