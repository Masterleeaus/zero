<?php

namespace Modules\Workflow\Console\Commands;

use Illuminate\Console\Command;
use Modules\Workflow\Entities\WorkflowRun;
use Modules\Workflow\Services\WorkflowEngine;

class WorkflowReplayRun extends Command
{
    protected $signature = 'workflow:replay {run_id}';
    protected $description = 'Replay a workflow run (for diagnostics).';

    public function handle(WorkflowEngine $engine): int
    {
        $runId = (int) $this->argument('run_id');
        $run = WorkflowRun::find($runId);

        if (!$run) {
            $this->error('Run not found.');
            return self::FAILURE;
        }

        $engine->run($run->trigger_key, $run->payload ?? [], [
            'company_id' => $run->company_id,
            'user_id' => $run->user_id,
            'replay_of' => $run->id,
        ]);

        $this->info('Replay dispatched.');
        return self::SUCCESS;
    }
}
