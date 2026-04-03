<?php

namespace Modules\Workflow\Triggers\Eloquent;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class EloquentUpdatedTrigger implements TriggerInterface
{
    public function key(): string { return 'eloquent.updated'; }
    public function label(): string { return 'Any model updated'; }

    public function schema(): array
    {
        return [
            'model_class' => 'string',
            'model_id' => 'int',
            'dirty' => 'array',
            'attributes' => 'array',
            'company_id' => 'int|null',
            'user_id' => 'int|null',
        ];
    }

    public function sample(): array
    {
        return [
            'model_class' => 'App\Models\Job',
            'model_id' => 456,
            'dirty' => ['status' => 'completed'],
            'attributes' => ['id' => 456, 'status' => 'completed'],
            'company_id' => 1,
            'user_id' => 5,
        ];
    }
}
