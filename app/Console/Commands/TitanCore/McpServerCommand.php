<?php

namespace App\Console\Commands\TitanCore;

use App\TitanCore\MCP\McpCapabilityRegistry;
use Illuminate\Console\Command;

class McpServerCommand extends Command
{
    protected $signature   = 'mcp:server {action=capabilities : capabilities|test|health}';
    protected $description = 'Manage and inspect the Titan MCP server';

    public function handle(McpCapabilityRegistry $registry): int
    {
        return match ($this->argument('action')) {
            'capabilities' => $this->showCapabilities($registry),
            'test'         => $this->runTest($registry),
            'health'       => $this->runHealth($registry),
            default        => $this->unknownAction(),
        };
    }

    private function showCapabilities(McpCapabilityRegistry $registry): int
    {
        $this->info('Titan MCP Server — Registered Capabilities');
        $this->line('');

        $rows = [];
        foreach ($registry->all() as $cap) {
            $rows[] = [
                $cap['name'],
                $cap['description'],
                $cap['auth'] ? '✓' : '✗',
                $cap['tenancy'] ? '✓' : '✗',
                $cap['approval_aware'] ? '✓' : '✗',
                $cap['queue'] ?? '—',
                $cap['rate_limit'],
            ];
        }

        $this->table(
            ['Name', 'Description', 'Auth', 'Tenancy', 'Approval', 'Queue', 'Rate/min'],
            $rows,
        );

        $this->line('');
        $this->info(count($rows).' capabilities registered.');

        $required = [
            'titan.ai.complete',
            'titan.memory.store',
            'titan.memory.recall',
            'titan.signal.dispatch',
            'titan.skill.dispatch',
            'titan.skill.status',
            'titan.skill.list',
        ];

        $missing = array_diff($required, $registry->names());

        if ($missing !== []) {
            $this->error('Missing required capabilities: '.implode(', ', $missing));
            return self::FAILURE;
        }

        $this->info('All required capabilities present. ✓');
        return self::SUCCESS;
    }

    private function runTest(McpCapabilityRegistry $registry): int
    {
        $this->info('Running MCP capability smoke test…');

        $required = [
            'titan.ai.complete',
            'titan.memory.store',
            'titan.memory.recall',
            'titan.signal.dispatch',
            'titan.skill.dispatch',
            'titan.skill.status',
            'titan.skill.list',
        ];

        $pass = true;
        foreach ($required as $cap) {
            $def = $registry->get($cap);
            if ($def === null) {
                $this->error("  FAIL  {$cap} — not registered");
                $pass = false;
                continue;
            }

            if (! class_exists($def['handler'])) {
                $this->error("  FAIL  {$cap} — handler class {$def['handler']} not found");
                $pass = false;
                continue;
            }

            $this->line("  PASS  {$cap}");
        }

        $this->line('');
        if ($pass) {
            $this->info('MCP capability test PASSED. ✓');
            return self::SUCCESS;
        }

        $this->error('MCP capability test FAILED.');
        return self::FAILURE;
    }

    private function runHealth(McpCapabilityRegistry $registry): int
    {
        $this->info('Titan MCP Server Health');
        $this->line('  Capabilities registered: '.count($registry->all()));
        $this->line('  ZYLOS_ENDPOINT: '.(config('titan_core.zylos.endpoint') ? '✓ set' : '✗ missing'));
        $this->line('  ZYLOS_SECRET: '.(config('titan_core.zylos.secret') ? '✓ set' : '✗ missing'));
        $this->line('  TITAN_DEFAULT_TEXT_MODEL: '.(config('titan_core.ai.default_text_model') ? '✓ set' : '✗ missing'));
        $this->line('  TITAN_MEMORY_TTL: '.config('titan_core.memory.ttl', '3600 (default)'));

        return self::SUCCESS;
    }

    private function unknownAction(): int
    {
        $this->error('Unknown action. Use: capabilities | test | health');
        return self::FAILURE;
    }
}
