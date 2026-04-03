<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\DocumentShareLink;

class DocumentShareLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.share') || $user->can('manage_documents');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.share') || $user->can('manage_documents');
    }

    public function revoke(User $user, DocumentShareLink $link): bool
    {
        return $user->can('documents.share') || $user->can('manage_documents');
    }
}
