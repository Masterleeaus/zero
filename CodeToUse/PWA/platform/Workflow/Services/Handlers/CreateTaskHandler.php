<?php

namespace Modules\Workflow\Services\Handlers;

use Modules\Workflow\Entities\WorkflowRunStep;
use Illuminate\Support\Facades\DB;

class CreateTaskHandler
{
    public function handle(WorkflowRunStep $step): void
    {
        // Minimal safe integration: create a core task if the tasks table exists.
        $cfg = $step->config ?? [];
        if (!DB::getSchemaBuilder()->hasTable('tasks')) {
            return;
        }

        DB::table('tasks')->insert([
            'heading' => $cfg['title'] ?? 'Workflow Task',
            'description' => $cfg['description'] ?? null,
            'user_id' => $cfg['user_id'] ?? null,
            'company_id' => $cfg['company_id'] ?? null,
            'status' => $cfg['status'] ?? 'incomplete',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
