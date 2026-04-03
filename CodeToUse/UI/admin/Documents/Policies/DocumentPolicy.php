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
}
