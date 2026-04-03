<?php

namespace Modules\Documents\Policies;

use App\Models\User;
use Modules\Documents\Entities\DocumentTemplate;

class DocumentTemplateGovernancePolicy
{
    public function manage(User $user): bool
    {
        return $user->can('documents.templates.manage') || $user->can('manage_documents');
    }

    public function publish(User $user, DocumentTemplate $template): bool
    {
        return $this->manage($user);
    }

    public function unpublish(User $user, DocumentTemplate $template): bool
    {
        return $this->manage($user);
    }
}
