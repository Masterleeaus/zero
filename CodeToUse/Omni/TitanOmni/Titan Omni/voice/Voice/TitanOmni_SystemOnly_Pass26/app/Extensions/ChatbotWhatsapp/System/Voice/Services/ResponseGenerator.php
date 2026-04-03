<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class ResponseGenerator
{
    public function missingInformation(VoiceCommand $command): string
    {
        $labels = [
            'customer_name' => 'Which customer is this for?',
            'subject' => 'What is it about?',
            'scheduled_for' => 'When should I schedule it?',
        ];

        $first = $command->missing[0] ?? null;

        return $labels[$first] ?? 'Can you give me a bit more detail?';
    }

    public function confirmation(VoiceCommand $command): string
    {
        return match ($command->intent) {
            'create_ticket' => sprintf('Create ticket for %s about %s. Confirm?', $command->entities['customer_name'] ?? 'this customer', $command->entities['subject'] ?? 'this issue'),
            'create_job' => sprintf('Create job for %s. Confirm?', $command->entities['customer_name'] ?? 'this customer'),
            'schedule_callback' => sprintf('Schedule callback for %s. Confirm?', $command->entities['scheduled_for'] ?? 'that time'),
            'list_tasks' => 'Show your current tasks. Confirm?',
            'update_status' => 'Update the status now. Confirm?',
            default => 'Please confirm this command.',
        };
    }

    public function completed(array $result): string
    {
        if (($result['status'] ?? false) !== true) {
            return (string) ($result['message'] ?? 'I could not complete that command.');
        }

        return (string) ($result['message'] ?? 'Done.');
    }

    public function permissionDenied(): string
    {
        return "I don't have permission to do that.";
    }
}
