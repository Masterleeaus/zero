<?php

namespace Modules\Workflow\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WorkflowRunDiagnostics extends Command
{
    protected $signature = 'workflow:diag';
    protected $description = 'Basic workflow diagnostics (tables + counts).';

    public function handle(): int
    {
        $tables = ['workflows', 'workflow_runs', 'workflow_run_steps'];
        foreach ($tables as $t) {
            $exists = DB::getSchemaBuilder()->hasTable($t);
            $this->line(($exists ? '[OK] ' : '[MISSING] ') . $t);
            if ($exists) {
                $this->line('  rows: ' . DB::table($t)->count());
            }
        }
        return self::SUCCESS;
    }
}
