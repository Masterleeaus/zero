<?php

namespace Modules\TitanTalk\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use Modules\TitanTalk\Models\TrainingPhrase;

class TrainingPhrasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, TrainingPhrase $tp): bool { return true; }
    public function create(User $user): bool { return true; }
    public function delete(User $user, TrainingPhrase $tp): bool { return true; }
}
