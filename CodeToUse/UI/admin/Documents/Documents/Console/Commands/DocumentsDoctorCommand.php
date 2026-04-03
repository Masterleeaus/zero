<?php

namespace Modules\Documents\Console\Commands;

use Illuminate\Console\Command;
use Modules\Documents\Services\Diagnostics\DocumentsHealthCheck;

class DocumentsDoctorCommand extends Command
{
    protected $signature = 'documents:doctor {--fix : Attempt safe, non-destructive fixes (creates missing folders only)}';

    protected $description = 'Runs a quick health check for the Documents module (routes, permissions, templates, storage paths).';

    public function handle(DocumentsHealthCheck $check): int
    {
        $this->info('Documents Doctor — starting checks...');

        $result = $check->run([
            'fix' => (bool) $this->option('fix'),
        ]);

        foreach ($result['messages'] as $msg) {
            $level = $msg['level'] ?? 'info';
            $text  = $msg['text'] ?? '';

            if ($level === 'error') $this->error($text);
            elseif ($level === 'warn') $this->warn($text);
            else $this->line($text);
        }

        $this->newLine();

        if ($result['ok']) {
            $this->info('✅ Documents Doctor: OK');
            return self::SUCCESS;
        }

        $this->error('❌ Documents Doctor: issues found');
        return self::FAILURE;
    }
}
