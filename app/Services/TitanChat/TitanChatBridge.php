<?php

namespace App\Services\TitanChat;

use App\Titan\Core\TitanMemoryService;
use App\TitanCore\Chat\Contracts\ChannelAdapterContract;
use App\TitanCore\Omni\OmniManager;
use Illuminate\Support\Str;

/**
 * TitanChatBridge
 *
 * The single service all chat surfaces (AIChatPro UI, Canvas UI, Chatbot widget,
 * channel adapters) call to execute a chat turn through the canonical Titan runtime.
 *
 * Pipeline:
 *   UI / Channel Adapter
 *       → TitanChatBridge::chat()
 *           → OmniManager::dispatch()
 *               → TitanAIRouter::execute()
 *                   → TitanMemory (recall + store)
 *                   → Signal / Approval / Rewind
 *
 * Rules:
 *  - Do NOT bypass this service with direct OpenAI/Bedrock calls for execution.
 *  - Memory reads/writes always go through TitanMemoryService (injected in TitanAIRouter).
 *  - Action generation (create jobs, invoices, etc.) must pass through the signal pipeline.
 */
class TitanChatBridge
{
    /** @var array<string, ChannelAdapterContract> */
    protected array $adapters = [];

    public function __construct(
        protected OmniManager $omni,
        protected TitanMemoryService $memory,
    ) {
    }

    /**
     * Register a channel adapter.
     */
    public function registerAdapter(ChannelAdapterContract $adapter): void
    {
        $this->adapters[$adapter->channel()] = $adapter;
    }

    /**
     * Execute a chat turn from any surface.
     *
     * @param  array<string, mixed>  $envelope  Normalised Titan envelope (see OmniManager::normaliseEnvelope)
     * @return array<string, mixed>
     */
    public function chat(array $envelope): array
    {
        return $this->omni->dispatch($envelope);
    }

    /**
     * Execute a chat turn from a named channel adapter.
     *
     * @param  string                $channel  e.g. 'messenger', 'whatsapp', 'telegram', 'voice', 'webchat', 'external'
     * @param  array<string, mixed>  $payload  raw channel-specific payload
     * @return array<string, mixed>
     */
    public function chatViaChannel(string $channel, array $payload): array
    {
        $adapter = $this->adapters[$channel] ?? null;

        if ($adapter === null) {
            return ['ok' => false, 'status' => 'unknown_channel', 'channel' => $channel];
        }

        $envelope = $adapter->toEnvelope($payload);
        $result   = $this->omni->dispatch($envelope);

        $adapter->sendResponse($result, $payload);

        return $result;
    }

    /**
     * Build a standard AIChatPro envelope from the common chat form fields.
     *
     * Called by AIChatProController (and AIChatController) when routing through
     * the canonical Titan path instead of direct provider calls.
     *
     * @param  array<string, mixed>  $params  validated request params
     * @return array<string, mixed>
     */
    public function buildChatProEnvelope(array $params): array
    {
        return [
            'id'           => (string) Str::uuid(),
            'intent'       => 'chat.complete',
            'stage'        => 'suggestion',
            'surface'      => 'aichatpro',
            'channel'      => 'workspace',
            'input'        => $params['input'] ?? $params['message'] ?? '',
            'company_id'   => $params['company_id'] ?? null,
            'user_id'      => $params['user_id'] ?? null,
            'session_id'   => $params['session_id'] ?? $params['chat_id'] ?? (string) Str::uuid(),
            'thread_id'    => $params['thread_id'] ?? null,
            'category_id'  => $params['category_id'] ?? $params['openai_chat_category_id'] ?? null,
            'model'        => $params['model'] ?? null,
            'attachments'  => $params['attachments'] ?? [],
            'memory_refs'  => $params['memory_refs'] ?? [],
        ];
    }

    /**
     * Build a standard Canvas envelope for a canvas reasoning / draft turn.
     *
     * @param  array<string, mixed>  $params  validated request params
     * @return array<string, mixed>
     */
    public function buildCanvasEnvelope(array $params): array
    {
        return [
            'id'           => (string) Str::uuid(),
            'intent'       => $params['intent'] ?? 'canvas.draft',
            'stage'        => 'suggestion',
            'surface'      => 'canvas',
            'channel'      => 'workspace',
            'input'        => $params['input'] ?? $params['prompt'] ?? '',
            'company_id'   => $params['company_id'] ?? null,
            'user_id'      => $params['user_id'] ?? null,
            'session_id'   => $params['session_id'] ?? $params['message_id'] ?? (string) Str::uuid(),
            'message_id'   => $params['message_id'] ?? null,
            'context_type' => $params['context_type'] ?? 'canvas',
            'memory_refs'  => $params['memory_refs'] ?? [],
        ];
    }

    /**
     * Recall memory for a chat session (convenience wrapper).
     *
     * @return array<string, mixed>
     */
    public function recallMemory(int $companyId, int $userId, string $sessionId): array
    {
        return $this->memory->hydrateContext([
            'company_id' => $companyId,
            'user_id'    => $userId,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Return all registered channel adapter identifiers.
     *
     * @return string[]
     */
    public function registeredChannels(): array
    {
        return array_keys($this->adapters);
    }
}
