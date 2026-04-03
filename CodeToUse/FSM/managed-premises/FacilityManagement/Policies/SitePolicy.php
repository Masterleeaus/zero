<?php
namespace Modules\FacilityManagement\Policies;
use Modules\FacilityManagement\Entities\Site;
use App\Models\User;

class SitePolicy {
  public function view(?User $user, Site $model = null) { return $user && $user->can('facilities.view'); }
  public function create(User $user) { return $user->can('facilities.manage'); }
  public function update(User $user, Site $model) { return $user->can('facilities.manage'); }
  public function delete(User $user, Site $model) { return $user->can('facilities.manage'); }
}