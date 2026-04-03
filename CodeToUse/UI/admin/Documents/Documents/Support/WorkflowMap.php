<?php

namespace Modules\Documents\Support;

use Modules\Documents\DTO\WorkflowTransition;

class WorkflowMap
{
    /**
     * Canonical workflow transitions for Documents.
     */
    public static function transitions(): array
    {
        return [
            new WorkflowTransition('draft', 'review', 'Send for review', 'documents.update'),
            new WorkflowTransition('review', 'approved', 'Approve', 'documents.approve'),
            new WorkflowTransition('approved', 'archived', 'Archive', 'documents.archive'),
            new WorkflowTransition('review', 'draft', 'Back to draft', 'documents.update'),
        ];
    }
}
