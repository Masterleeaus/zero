<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\CreateJobCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\CreateTicketCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\GenericCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\ListTasksCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\ScheduleCallbackCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\UpdateStatusCommandHandler;

class IntentRouter
{
    public function __construct(
        protected GenericCommandHandler $fallbackHandler,
        protected CreateTicketCommandHandler $createTicketHandler,
        protected CreateJobCommandHandler $createJobHandler,
        protected ListTasksCommandHandler $listTasksHandler,
        protected ScheduleCallbackCommandHandler $scheduleCallbackHandler,
        protected UpdateStatusCommandHandler $updateStatusHandler,
    ) {}

    public function route(VoiceCommand $command, ChatbotConversation $conversation): array
    {
        return match ($command->intent) {
            'create_ticket' => $this->createTicketHandler->handle($command, $conversation),
            'create_job' => $this->createJobHandler->handle($command, $conversation),
            'list_tasks' => $this->listTasksHandler->handle($command, $conversation),
            'schedule_callback' => $this->scheduleCallbackHandler->handle($command, $conversation),
            'update_status' => $this->updateStatusHandler->handle($command, $conversation),
            default => $this->fallbackHandler->handle($command, $conversation),
        };
    }
}
