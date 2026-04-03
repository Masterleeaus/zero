<?php

namespace App\TitanCore\MCP;

/**
 * McpCapabilityRegistry — defines all registered MCP tool capabilities.
 *
 * Every capability maps to a handler that can be invoked via the MCP
 * transport layer (HTTP or WebSocket). Each entry carries auth and
 * tenancy requirements so the MCP server can enforce them before
 * delegating to the underlying service.
 */
class McpCapabilityRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return [
            'titan.ai.complete' => [
                'name'        => 'titan.ai.complete',
                'description' => 'Execute an AI completion through TitanAIRouter',
                'handler'     => \App\TitanCore\MCP\Handlers\AiCompleteHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => true,
                'queue'       => 'titan-ai',
                'rate_limit'  => 60,
            ],
            'titan.memory.store' => [
                'name'        => 'titan.memory.store',
                'description' => 'Store a memory entry via TitanMemoryService',
                'handler'     => \App\TitanCore\MCP\Handlers\MemoryStoreHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => false,
                'queue'       => 'titan-ai',
                'rate_limit'  => 120,
            ],
            'titan.memory.recall' => [
                'name'        => 'titan.memory.recall',
                'description' => 'Recall a memory entry via TitanMemoryService',
                'handler'     => \App\TitanCore\MCP\Handlers\MemoryRecallHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => false,
                'queue'       => null,
                'rate_limit'  => 120,
            ],
            'titan.signal.dispatch' => [
                'name'        => 'titan.signal.dispatch',
                'description' => 'Dispatch a Titan signal through the signal pipeline',
                'handler'     => \App\TitanCore\MCP\Handlers\SignalDispatchHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => true,
                'queue'       => 'titan-signals',
                'rate_limit'  => 60,
            ],
            'titan.skill.dispatch' => [
                'name'        => 'titan.skill.dispatch',
                'description' => 'Dispatch a skill execution through ZylosBridge',
                'handler'     => \App\TitanCore\MCP\Handlers\SkillDispatchHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => true,
                'queue'       => 'titan-skills',
                'rate_limit'  => 30,
            ],
            'titan.skill.status' => [
                'name'        => 'titan.skill.status',
                'description' => 'Query the status of a dispatched skill execution',
                'handler'     => \App\TitanCore\MCP\Handlers\SkillStatusHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => false,
                'queue'       => null,
                'rate_limit'  => 120,
            ],
            'titan.skill.list' => [
                'name'        => 'titan.skill.list',
                'description' => 'List available skills from the Zylos runtime',
                'handler'     => \App\TitanCore\MCP\Handlers\SkillListHandler::class,
                'auth'        => true,
                'tenancy'     => true,
                'approval_aware' => false,
                'queue'       => null,
                'rate_limit'  => 30,
            ],
        ];
    }

    /**
     * Retrieve a single capability definition by name.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $name): ?array
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Return just the capability names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->all());
    }
}
