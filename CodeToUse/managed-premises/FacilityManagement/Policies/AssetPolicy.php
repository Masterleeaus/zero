<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Asset;
use App\Models\User;

class AssetPolicy {
  public function view(?User $user, Asset $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Asset $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Asset $model) { return $user->can('facilities.manage'); }
}