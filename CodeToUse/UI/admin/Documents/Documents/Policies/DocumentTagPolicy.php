<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\DocumentTag;

class DocumentTagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.tags.manage');
    }

    public function delete(User $user, DocumentTag $tag): bool
    {
        return $user->can('documents.tags.manage');
    }
}
