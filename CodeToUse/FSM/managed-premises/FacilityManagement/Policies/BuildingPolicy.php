<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Building;
use App\Models\User;

class BuildingPolicy {
  public function view(?User $user, Building $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Building $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Building $model) { return $user->can('facilities.manage'); }
}