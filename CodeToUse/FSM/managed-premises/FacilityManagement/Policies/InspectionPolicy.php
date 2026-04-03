<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Inspection;
use App\Models\User;

class InspectionPolicy {
  public function view(?User $user, Inspection $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Inspection $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Inspection $model) { return $user->can('facilities.manage'); }
}