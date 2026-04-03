<?php

namespace Modules\Workflow\Triggers\Core;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class ManualTrigger implements TriggerInterface
{
    public function key(): string { return 'manual'; }
    public function label(): string { return 'Manual (Run Now)'; }
    public function schema(): array { return ['company_id'=>'int|null','user_id'=>'int|null']; }
    public function sample(): array { return ['company_id'=>1,'user_id'=>5]; }
}
