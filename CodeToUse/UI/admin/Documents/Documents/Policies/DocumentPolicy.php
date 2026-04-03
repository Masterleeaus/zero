<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\Document;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return (int) ($user->company_id ?? $user->id) === (int) $document->tenant_id;
    }

    public function update(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function approve(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->can('documents.approve');
    }

    public function archive(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->can('documents.archive');
    }

    public function version(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->can('documents.version');
    }

    public function restore(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->can('documents.restore');
    }

    public function link(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->can('documents.link');
    }
}
