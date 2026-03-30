<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Occupancy;
use App\Models\User;

class OccupancyPolicy {
  public function view(?User $user, Occupancy $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Occupancy $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Occupancy $model) { return $user->can('facilities.manage'); }
}