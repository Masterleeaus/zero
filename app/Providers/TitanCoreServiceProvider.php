<?php

namespace App\Providers;

use App\Titan\Core\Mcp\Tools\MemoryRecallTool;
use App\Titan\Core\Mcp\Tools\MemoryStoreTool;
use App\Titan\Core\TitanMemoryService;
use App\Titan\Core\Vector\VectorMemoryAdapter;
use App\TitanCore\Agents\AgentStudioManager;
use App\TitanCore\MCP\McpCapabilityRegistry;
use App\TitanCore\Omni\OmniManager;
use App\TitanCore\Pulse\PulseManager;
use App\TitanCore\Registry\CoreManifest;
use App\TitanCore\Registry\CoreModuleDefinition;
use App\TitanCore\Registry\CoreModuleRegistry;
use App\TitanCore\Registry\Runtime\RuntimeCatalog;
use App\TitanCore\Registry\Runtime\RuntimeDefinition;
use App\TitanCore\Registry\Tools\ToolDefinition;
use App\TitanCore\Registry\Tools\ToolRegistry;
use App\TitanCore\Support\CoreSourceCatalog;
use App\TitanCore\Zero\AI\Consensus\ConsensusCoordinator;
use App\TitanCore\Zero\AI\Context\DecisionContextFactory;
use App\TitanCore\Zero\AI\Context\InstructionBuilder;
use App\TitanCore\Zero\AI\Nexus\AuthorityWeights;
use App\TitanCore\Zero\AI\Nexus\CritiqueLoopEngine;
use App\TitanCore\Zero\AI\Nexus\NexusCoordinator;
use App\TitanCore\Zero\AI\Nexus\RoundRobinRefinement;
use App\TitanCore\Zero\AI\Nexus\UnifiedContextPackBuilder;
use App\TitanCore\Zero\AI\Runtime\NullRuntimeAdapter;
use App\TitanCore\Zero\AI\Runtime\RuntimeManager;
use App\TitanCore\Zero\AI\TitanAIRouter;
use App\TitanCore\Zero\AI\ZeroCoreManager;
use App\TitanCore\Zero\Budget\TitanTokenBudget;
use App\TitanCore\Zero\CoreKernel;
use App\TitanCore\Zero\Knowledge\KnowledgeManager;
use App\TitanCore\Zero\Knowledge\KnowledgeScopeResolver;
use App\TitanCore\Zero\Memory\MemoryManager;
use App\TitanCore\Zero\Memory\Session\SessionHandoffManager;
use App\TitanCore\Zero\Process\ProcessBridge;
use App\TitanCore\Zero\Rewind\RewindManager;
use App\TitanCore\Zero\Signals\SignalBridge;
use App\TitanCore\Zero\Telemetry\TelemetryManager;
use App\TitanCore\Zylos\ZylosBridge;
use App\Services\Omni\OmniAnalyticsService;
use App\Services\Omni\OmniChannelService;
use App\Services\Omni\OmniConversationService;
use App\Services\Omni\OmniInboxService;
use App\Services\Omni\OmniKnowledgeService;
use Illuminate\Support\ServiceProvider;

/**
 * TitanCoreServiceProvider — canonical service container bindings for Titan Core.
 *
 * Canonical singletons:
 *   Memory runtime     → App\Titan\Core\TitanMemoryService (DB-backed, rewind-compatible)
 *   Zylos bridge       → App\TitanCore\Zylos\ZylosBridge (dispatch + admin monitor)
 *   AI router          → App\TitanCore\Zero\AI\TitanAIRouter
 *   MCP tools          → App\Titan\Core\Mcp\Tools\*
 *   ZeroCoreManager    → App\TitanCore\Zero\AI\ZeroCoreManager
 *
 * Deprecated paths (tombstones in place, not bound here):
 *   App\TitanCore\Zero\Memory\TitanMemoryService  — superseded by App\Titan\Core\TitanMemoryService
 *   App\TitanCore\Zero\Skills\ZylosBridge         — superseded by App\TitanCore\Zylos\ZylosBridge
 */
class TitanCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Config ───────────────────────────────────────────────────────────
        $this->mergeConfigFrom(base_path('config/titan_core.php'), 'titan_core');
        $this->mergeConfigFrom(base_path('config/titan_ai.php'), 'titan_ai');
        $this->mergeConfigFrom(base_path('config/titan_budgets.php'), 'titan_budgets');
        $this->mergeConfigFrom(base_path('config/titan_omni.php'), 'titan_omni');

        // Optional legacy config files — skip gracefully if absent
        if (file_exists(base_path('config/titan_process.php'))) {
            $this->mergeConfigFrom(base_path('config/titan_process.php'), 'titan_process');
        }
        if (file_exists(base_path('config/titan_memory.php'))) {
            $this->mergeConfigFrom(base_path('config/titan_memory.php'), 'titan_memory');
        }

        // ── Module registry ─────────────────────────────────────────────────
        $this->app->singleton(CoreModuleRegistry::class, function () {
            $registry = new CoreModuleRegistry();
            $registry->register(new CoreModuleDefinition('zero', 'Titan Zero Core', [], 10, ['surface' => 'governance']));
            $registry->register(new CoreModuleDefinition('knowledge', 'Shared Knowledge Core', ['zero'], 20, ['surface' => 'retrieval']));
            $registry->register(new CoreModuleDefinition('memory', 'Shared Memory Core', ['zero'], 30, ['surface' => 'memory']));
            $registry->register(new CoreModuleDefinition('pulse', 'Titan Pulse', ['zero'], 40, ['surface' => 'automation']));
            $registry->register(new CoreModuleDefinition('omni', 'Titan Omni', ['zero', 'knowledge', 'memory'], 50, ['surface' => 'channels']));
            $registry->register(new CoreModuleDefinition('agents', 'Agent Studio', ['zero', 'pulse', 'omni'], 60, ['surface' => 'agents']));
            return $registry;
        });

        // ── Runtime catalog ──────────────────────────────────────────────────
        $this->app->singleton(RuntimeCatalog::class, function () {
            $catalog = new RuntimeCatalog();
            $catalog->register(new RuntimeDefinition('null', 'Null Runtime', NullRuntimeAdapter::class, ['bootstrap']));
            $catalog->register(new RuntimeDefinition('openai', 'OpenAI Runtime', NullRuntimeAdapter::class, ['reasoning', 'chat', 'tools']));
            $catalog->register(new RuntimeDefinition('openrouter', 'OpenRouter Runtime', NullRuntimeAdapter::class, ['routing', 'multi-model']));
            $catalog->register(new RuntimeDefinition('local', 'Local Device Runtime', NullRuntimeAdapter::class, ['offline', 'voice', 'edge']));
            return $catalog;
        });

        // ── Tool registry ────────────────────────────────────────────────────
        $this->app->singleton(ToolRegistry::class, function () {
            $registry = new ToolRegistry();
            $registry->register(new ToolDefinition('signal.record', 'Signal Record', ProcessBridge::class, ['zero', 'pulse']));
            $registry->register(new ToolDefinition('signal.publish', 'Signal Publish', SignalBridge::class, ['zero', 'omni']));
            $registry->register(new ToolDefinition('rewind.begin', 'Rewind Begin', RewindManager::class, ['zero']));
            $registry->register(new ToolDefinition('pulse.schedule', 'Pulse Schedule', PulseManager::class, ['pulse']));
            $registry->register(new ToolDefinition('omni.ingest', 'Omni Ingest', OmniManager::class, ['omni']));
            $registry->register(new ToolDefinition('agent.draft', 'Agent Draft', AgentStudioManager::class, ['agents']));
            $registry->register(new ToolDefinition('memory.recall', 'Memory Recall', MemoryRecallTool::class, ['zero', 'memory']));
            $registry->register(new ToolDefinition('memory.store', 'Memory Store', MemoryStoreTool::class, ['zero', 'memory']));
            return $registry;
        });

        // ── Canonical Zylos bridge ────────────────────────────────────────────
        $this->app->singleton(ZylosBridge::class, function ($app) {
            return new ZylosBridge($app->make(\Illuminate\Http\Client\Factory::class));
        });

        // ── Canonical memory runtime ──────────────────────────────────────────
        $this->app->singleton(VectorMemoryAdapter::class);
        $this->app->singleton(TitanMemoryService::class);
        $this->app->singleton(MemoryRecallTool::class);
        $this->app->singleton(MemoryStoreTool::class);

        // ── Supporting memory infrastructure ─────────────────────────────────
        $this->app->singleton(SessionHandoffManager::class);
        $this->app->singleton(MemoryManager::class);

        // ── AI layer ─────────────────────────────────────────────────────────
        $this->app->singleton(RuntimeManager::class);
        $this->app->singleton(InstructionBuilder::class);
        $this->app->singleton(DecisionContextFactory::class);
        $this->app->singleton(ConsensusCoordinator::class);
        $this->app->singleton(AuthorityWeights::class);
        $this->app->singleton(CritiqueLoopEngine::class);
        $this->app->singleton(RoundRobinRefinement::class);
        $this->app->singleton(UnifiedContextPackBuilder::class);
        $this->app->singleton(NexusCoordinator::class);
        $this->app->singleton(KnowledgeScopeResolver::class);
        $this->app->singleton(KnowledgeManager::class);
        $this->app->singleton(TelemetryManager::class);
        $this->app->singleton(TitanTokenBudget::class);
        $this->app->singleton(ZeroCoreManager::class);
        $this->app->singleton(TitanAIRouter::class);

        // ── Pipeline layer ───────────────────────────────────────────────────
        $this->app->singleton(SignalBridge::class);
        $this->app->singleton(ProcessBridge::class);
        $this->app->singleton(RewindManager::class);
        $this->app->singleton(PulseManager::class);
        $this->app->singleton(OmniManager::class);
        $this->app->singleton(AgentStudioManager::class);

        // ── Omni services ────────────────────────────────────────────────────
        $this->app->singleton(OmniConversationService::class);
        $this->app->singleton(OmniChannelService::class);
        $this->app->singleton(OmniKnowledgeService::class);
        $this->app->singleton(OmniInboxService::class);
        $this->app->singleton(OmniAnalyticsService::class);

        // ── Omni driver registry ──────────────────────────────────────────────
        $this->app->singleton(\App\Services\Omni\OmniDriverRegistry::class, function ($app) {
            $registry = new \App\Services\Omni\OmniDriverRegistry();

            $registry->register(new \App\Services\Drivers\SmsDriver(
                config('titan_omni.drivers.sms', [])
            ));
            $registry->register(new \App\Services\Drivers\EmailDriver(
                config('titan_omni.drivers.email', [])
            ));
            $registry->register(new \App\Services\Drivers\WhatsAppMetaDriver(
                config('titan_omni.drivers.whatsapp_meta', [])
            ));
            $registry->register(new \App\Services\Drivers\WhatsAppTwilioDriver(
                config('titan_omni.drivers.whatsapp_twilio', [])
            ));
            $registry->register(new \App\Services\Drivers\TelegramDriver(
                config('titan_omni.drivers.telegram', [])
            ));
            $registry->register(new \App\Services\Drivers\WebchatDriver());
            $registry->register(new \App\Services\Drivers\VoiceDriver(
                config('titan_omni.drivers.voice', [])
            ));

            return $registry;
        });

        // ── Chat Bridge ──────────────────────────────────────────────────────
        // Canonical bridge: all chat surfaces (AIChatPro, Canvas, Chatbot, channels)
        // route execution through TitanChatBridge → OmniManager → TitanAIRouter.
        $this->app->singleton(\App\Services\TitanChat\TitanChatBridge::class, function ($app) {
            $bridge = new \App\Services\TitanChat\TitanChatBridge(
                $app->make(OmniManager::class),
                $app->make(TitanMemoryService::class),
            );

            // Register all channel adapters
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\MessengerChannelAdapter::class));
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\WhatsAppChannelAdapter::class));
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\TelegramChannelAdapter::class));
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\VoiceChannelAdapter::class));
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\WebchatChannelAdapter::class));
            $bridge->registerAdapter($app->make(\App\TitanCore\Chat\Channels\ExternalChatbotChannelAdapter::class));

            return $bridge;
        });

        // ── Registry / manifest / MCP ────────────────────────────────────────
        $this->app->singleton(CoreSourceCatalog::class);
        $this->app->singleton(CoreManifest::class);
        $this->app->singleton(McpCapabilityRegistry::class);

        // ── Kernel ───────────────────────────────────────────────────────────
        $this->app->singleton(CoreKernel::class);
    }

    public function boot(): void
    {
    }
}
