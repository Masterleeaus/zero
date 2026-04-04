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
        protected LifecycleStageResolver $stageResolver,
    ) {}

    public function handle(ChatbotConversation $conversation, string $transcript, string $channel = 'voice'): UnifiedActionResult
    {
        $command = $this->parser->parse($transcript);
        $persona = $this->personaResolver->resolve($conversation, $channel, $command->intent);
        $stage = $this->stageResolver->resolve($command->intent);

        if ($command->isIncomplete()) {
            $response = $this->responseGenerator->missingInformation($command->intent, $command->missing);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'missing', $stage);

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['missing' => $command->missing, 'lifecycle_stage' => $stage], true, false);
        }

        if ($command->confidence < (float) config('unified-communication.features.intent_confirmation_threshold', 0.60)) {
            $queued = $this->offlineSyncQueueService->queue($conversation->id, $channel, $transcript, ['reason' => 'low_confidence', 'lifecycle_stage' => $stage]);
            $response = $this->aiFallbackService->respond($transcript, $conversation, $persona);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'fallback', $stage);

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['offline_action_id' => $queued->id, 'lifecycle_stage' => $stage], false, true);
        }

        if (!$this->permissionManager->canExecute($conversation, $command->intent)) {
            $response = $this->responseGenerator->permissionDenied($command->intent);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'denied', $stage);

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['lifecycle_stage' => $stage]);
        }

        $this->contextManager->remember($conversation->id, [
            'last_intent' => $command->intent,
            'entities' => $command->entities,
            'persona' => $persona,
            'lifecycle_stage' => $stage,
        ]);

        $requiresConfirmation = $command->confidence < (float) config('unified-communication.features.intent_execute_threshold', 0.90);
        if ($requiresConfirmation) {
            $response = $this->responseGenerator->confirm($command->intent, $command->entities);
            $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'confirm', $stage);

            return new UnifiedActionResult(true, $response, $command->intent, $persona, ['lifecycle_stage' => $stage], true, false);
        }

        $result = $this->router->route($command, $conversation);
        $response = $this->responseGenerator->success($command->intent, $result);
        $this->log($conversation->id, $transcript, $command->intent, $command->entities, $command->confidence, 'executed', $stage);

        return new UnifiedActionResult(true, $response, $command->intent, $persona, ['result' => $result, 'lifecycle_stage' => $stage]);
    }

    protected function log(int $conversationId, string $transcript, ?string $intent, array $entities, float $confidence, string $status, string $stage): void
    {
        VoiceCommandLog::query()->create([
            'conversation_id' => $conversationId,
            'transcript' => $transcript,
            'parsed_intent' => $intent,
            'entities' => array_merge($entities, ['lifecycle_stage' => $stage]),
            'confidence' => $confidence,
            'status' => $status,
            'duration_ms' => 0,
        ]);
    }
}
