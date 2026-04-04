<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Support;

use App\Extensions\Chatbot\System\Models\Chatbot;

class WorkspaceResolver
{
    public function resolveForChatbot(Chatbot $chatbot): array
    {
        $workspaceId = $chatbot->workspace_id ?: $chatbot->company_id ?: $chatbot->team_id ?: $chatbot->user_id;
        $companyId = $chatbot->company_id ?: $workspaceId;
        $teamId = $chatbot->team_id ?: $companyId;

        return [
            'workspace_id' => $workspaceId ? (int) $workspaceId : null,
            'company_id' => $companyId ? (int) $companyId : null,
            'team_id' => $teamId ? (int) $teamId : null,
        ];
    }
}
