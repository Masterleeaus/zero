<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentTemplatePolicy
{
    public function view(User $user, DocumentTemplate $template): bool
    {
        return $template->tenant_id === null
            || (int) $template->tenant_id === (int) ($user->company_id ?? $user->id);
    }

    public function create(User $user): bool
    {
        return (bool) $user->id;
    }

    public function update(User $user, DocumentTemplate $template): bool
    {
        return $this->view($user, $template);
    }

    public function delete(User $user, DocumentTemplate $template): bool
    {
        return $this->view($user, $template);
    }
}
