<?php

declare(strict_types=1);

namespace Extensions\TitanHello\Services;

use App\Services\Ai\ElevenLabsService;
use Illuminate\Http\JsonResponse;

/**
 * ElevenLabs agent/knowledgebase operations extracted/adapted from the ElevenLabsVoiceChat extension.
 *
 * This service is deliberately "thin":
 * - it builds the ElevenLabs conversation_config payloads
 * - it calls the app-level ElevenLabsService (which encapsulates HTTP/auth)
 */
class ElevenLabsAgentService
{
    public function __construct(protected ElevenLabsService $service) {}

    /**
     * Create an ElevenLabs Conversational AI agent.
     *
     * @param array{title:string,welcome_message:string,instructions:string,language?:string|null,voice_id?:string|null} $args
     */
    public function createAgent(array $args): JsonResponse
    {
        $conversation_config = [
            'agent' => [
                'first_message' => (string) ($args['welcome_message'] ?? 'Hi, how can I help?'),
                'prompt'        => [
                    'prompt' => (string) ($args['instructions'] ?? 'You are a helpful phone receptionist for a tradie business.'),
                ],
            ],
            'tts' => [
                // For English, ElevenLabsService often exposes a default model constant.
                'model_id' => ElevenLabsService::DEFAULT_ELEVENLABS_MODEL_FOR_ENGLISH,
            ],
            'conversation' => [
                // These events are useful if the underlying ElevenLabsService supports them.
                'client_events' => ['audio', 'interruption', 'user_transcript', 'agent_response'],
            ],
        ];

        if (!empty($args['voice_id'])) {
            $conversation_config['tts']['voice_id'] = (string) $args['voice_id'];
        }

        $lang = $args['language'] ?? null;
        if ($lang && $lang !== 'auto') {
            $conversation_config['agent']['language'] = (string) $lang;
            if ((string) $lang !== 'en') {
                $conversation_config['tts']['model_id'] = ElevenLabsService::DEFAULT_ELEVENLABS_MODEL;
            }
        }

        return $this->service->createAgent(
            conversation_config: $conversation_config,
            name: (string) ($args['title'] ?? 'Titan Hello Agent')
        );
    }

    /**
     * Update an agent's core conversation config (prompt, voice, language).
     */
    public function updateAgent(
        string $agentRemoteId,
        array $conversationConfig,
        ?string $name = null
    ): JsonResponse {
        return $this->service->updateAgent(
            agent_id: $agentRemoteId,
            conversation_config: $conversationConfig,
            name: $name
        );
    }

    /**
     * Fetch list of voices.
     */
    public function getVoices(int $pageSize = 100): array
    {
        $res = $this->service->getListOfVoices(page_size: $pageSize);
        if ($res->getData()?->status === 'success') {
            return (array) ($res->getData()->resData->voices ?? []);
        }
        return [];
    }

    public function deleteKnowledgebase(string $docId): JsonResponse
    {
        return $this->service->deleteKnowledgebaseDocument($docId);
    }

    /**
     * @param 'text'|'url'|'file' $type
     * @param mixed $content
     */
    public function addKnowledgebase(string $type, mixed $content, ?string $name = null): JsonResponse
    {
        return match ($type) {
            'text' => $this->service->createKnowledgebaseDocFromText(text: (string) $content, name: $name),
            'url'  => $this->service->createKnowledgebaseDocFromUrl(url: (string) $content, name: $name),
            'file' => $this->service->createKnowledgebaseDocFromFile(file: $content, name: $name),
            default => response()->json([
                'status' => 'error',
                'message' => 'Unsupported knowledgebase type',
            ], 422),
        };
    }

    /**
     * Build a minimal config payload that attaches trained knowledgebase docs.
     *
     * @param array<int,array{id:string,name:string,type:string}> $trainedKnowledges
     */
    public function buildKnowledgebaseConfig(array $trainedKnowledges): array
    {
        return [
            'agent' => [
                'prompt' => [
                    'knowledge_base' => $trainedKnowledges,
                ],
            ],
        ];
    }
}
