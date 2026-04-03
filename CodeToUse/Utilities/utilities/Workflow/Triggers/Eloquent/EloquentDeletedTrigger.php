<?php

namespace Modules\Workflow\Triggers\Eloquent;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class EloquentDeletedTrigger implements TriggerInterface
{
    public function key(): string { return 'eloquent.deleted'; }
    public function label(): string { return 'Any model deleted'; }

    public function schema(): array
    {
        return [
            'model_class' => 'string',
            'model_id' => 'int',
            'attributes' => 'array',
            'company_id' => 'int|null',
            'user_id' => 'int|null',
        ];
    }

    public function sample(): array
    {
        return [
            'model_class' => 'App\Models\Task',
            'model_id' => 789,
            'attributes' => ['id' => 789],
            'company_id' => 1,
            'user_id' => 5,
        ];
    }
}
