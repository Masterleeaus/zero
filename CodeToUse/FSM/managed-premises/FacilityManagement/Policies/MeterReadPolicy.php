<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\MeterRead;
use App\Models\User;

class MeterReadPolicy {
  public function view(?User $user, MeterRead $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, MeterRead $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, MeterRead $model) { return $user->can('facilities.manage'); }
}