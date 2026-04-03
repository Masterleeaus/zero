<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\DocumentRequest;

class DocumentRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.requests.manage');
    }

    public function view(User $user, DocumentRequest $req): bool
    {
        return $user->can('documents.requests.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.requests.manage');
    }

    public function send(User $user, DocumentRequest $req): bool
    {
        return $user->can('documents.requests.send');
    }

    public function cancel(User $user, DocumentRequest $req): bool
    {
        return $user->can('documents.requests.manage');
    }
}
