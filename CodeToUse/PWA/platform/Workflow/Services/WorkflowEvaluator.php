<?php

namespace Modules\Workflow\Services;

use Modules\Workflow\Conditions\ConditionRegistry;
use Modules\Workflow\Entities\Workflow;

class WorkflowEvaluator
{
    public function __construct(protected ConditionRegistry $conditions)
    {
    }

    public function shouldRun(Workflow $workflow, array $payload): bool
    {
        $conds = $workflow->conditions_json ? json_decode($workflow->conditions_json, true) : [];
        if (!is_array($conds) || empty($conds)) {
            return true;
        }
        return $this->conditions->evaluateAll($payload, $conds);
    }
}
