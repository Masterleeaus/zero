<?php

namespace Modules\Workflow\Triggers\Eloquent;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class EloquentCreatedTrigger implements TriggerInterface
{
    public function key(): string { return 'eloquent.created'; }

    public function label(): string { return 'Any model created'; }

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
            'model_class' => 'App\Models\Invoice',
            'model_id' => 123,
            'attributes' => ['id' => 123],
            'company_id' => 1,
            'user_id' => 5,
        ];
    }
}
