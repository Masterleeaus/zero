<?php

namespace Modules\Workflow\Triggers\Core;

use Modules\Workflow\Triggers\Contracts\TriggerInterface;

class ScheduledTrigger implements TriggerInterface
{
    public function key(): string { return 'scheduled'; }
    public function label(): string { return 'Scheduled'; }
    public function schema(): array { return ['cron'=>'string','company_id'=>'int|null']; }
    public function sample(): array { return ['cron'=>'0 7 * * *','company_id'=>1]; }
}
