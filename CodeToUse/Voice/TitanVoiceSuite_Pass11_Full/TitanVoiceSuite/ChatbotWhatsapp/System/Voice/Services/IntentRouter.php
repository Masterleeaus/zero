<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\AssignTechnicianHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\CloseJobHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\CreateInvoiceHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\CreateQuoteHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\GenericCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\LifecycleAwareHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\UpdateScheduleHandler;

class IntentRouter
{
    /**
     * @var array<string, LifecycleAwareHandler>
     */
    protected array $handlers = [];

    public function __construct(
        protected GenericCommandHandler $genericHandler,
        CreateQuoteHandler $createQuoteHandler,
        CreateInvoiceHandler $createInvoiceHandler,
        AssignTechnicianHandler $assignTechnicianHandler,
        CloseJobHandler $closeJobHandler,
        UpdateScheduleHandler $updateScheduleHandler,
        protected LifecycleStageResolver $stageResolver,
    ) {
        $this->handlers = [
            'create_quote' => $createQuoteHandler,
            'create_invoice' => $createInvoiceHandler,
            'assign_technician' => $assignTechnicianHandler,
            'close_job' => $closeJobHandler,
            'update_schedule' => $updateScheduleHandler,
        ];
    }

    public function route(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        $stage = $this->stageResolver->resolve($command->intent);

        if (isset($this->handlers[$command->intent])) {
            $result = $this->handlers[$command->intent]->handle($command, $conversation);
            $result['lifecycle_stage'] = $result['lifecycle_stage'] ?? $stage;

            return $result;
        }

        $result = $this->genericHandler->handle($command, $conversation);
        $result['lifecycle_stage'] = $result['lifecycle_stage'] ?? $stage;

        return $result;
    }
}
