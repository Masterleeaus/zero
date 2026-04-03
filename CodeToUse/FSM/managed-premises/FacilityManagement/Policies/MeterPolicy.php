<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Meter;
use App\Models\User;

class MeterPolicy {
  public function view(?User $user, Meter $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Meter $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Meter $model) { return $user->can('facilities.manage'); }
}