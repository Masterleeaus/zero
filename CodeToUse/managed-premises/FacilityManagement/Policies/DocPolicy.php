<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Doc;
use App\Models\User;

class DocPolicy {
  public function view(?User $user, Doc $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Doc $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Doc $model) { return $user->can('facilities.manage'); }
}