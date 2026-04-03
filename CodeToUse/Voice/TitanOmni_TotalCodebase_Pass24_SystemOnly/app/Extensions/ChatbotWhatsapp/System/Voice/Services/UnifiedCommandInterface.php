<?php

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Models\VoiceCommandLog;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\UnifiedActionResult;

class UnifiedCommandInterface
{
    public function __construct(
        protected VoiceCommandParser $parser,
        protected IntentRouter $router,
        protected PermissionManager $permissionManager,
        protected ResponseGenerator $responseGenerator,
        protected ContextManager $contextManager,
        protected PersonaResolver $personaResolver,
        protected AiFallbackService $aiFallbackService,
        protected OfflineSyncQueueService $offlineSyncQueueService,
    ) {}

    public function handle(ChatbotConversation $conversation, string $transcript, string $channel = 'voice'): UnifiedActionResult
    {
        $command = $this->parser->parse($transcript);
        $persona = $this->personaResolver->resolve($conversation, $channel, $command->intent);

        if (!empty($command->missing)) {
            $response = $this->responseGenerator->missingInformation($command);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'missing_fields');

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['missing' => $command->missing], true, false);
        }

        if ($command->confidence < (float) config('unified-communication.features.intent_confirmation_threshold', 0.60)) {
            $queued = $this->offlineSyncQueueService->queue($conversation->id, $channel, $transcript, ['reason' => 'low_confidence']);
            $response = $this->aiFallbackService->respond($transcript, $conversation, $persona);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'fallback');

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['offline_action_id' => $queued->id], false, true);
        }

        if (!$this->permissionManager->canExecute($conversation, $command->intent)) {
            $response = $this->responseGenerator->permissionDenied($command->intent);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'denied');

            return new UnifiedActionResult(true, $response, $command->intent, $persona);
        }

        $this->contextManager->remember($conversation->id, [
            'last_intent' => $command->intent,
            'entities' => $command->entities,
            'persona' => $persona,
        ]);

        $requiresConfirmation = $command->confidence < (float) config('unified-communication.features.intent_execute_threshold', 0.90);
        if ($requiresConfirmation) {
            $response = $this->responseGenerator->confirm($command->intent, $command->entities);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'confirm');

            return new UnifiedActionResult(true, $response, $command->intent, $persona, [], true, false);
        }

        $result = $this->router->route($command, $conversation);
        $response = $this->responseGenerator->success($command->intent, $result);
        $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'executed');

        return new UnifiedActionResult(true, $response, $command->intent, $persona, ['result' => $result]);
    }

    protected function log(int $conversationId, string $transcript, ?string $intent, array $entities, float $confidence, string $status): void
    {
        VoiceCommandLog::query()->create([
            'conversation_id' => $conversationId,
            'transcript' => $transcript,
            'parsed_intent' => $intent,
            'entities' => $entities,
            'confidence' => $confidence,
            'status' => $status,
            'duration_ms' => 0,
        ]);
    }
}
