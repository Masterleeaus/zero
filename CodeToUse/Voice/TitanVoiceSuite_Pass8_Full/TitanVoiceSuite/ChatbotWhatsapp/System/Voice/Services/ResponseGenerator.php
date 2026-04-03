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
            'status' => 'What status should I set?',
        ];

        $first = $command->missing[0] ?? null;

        return $labels[$first] ?? 'Can you give me a bit more detail?';
    }

    public function confirmation(VoiceCommand $command): string
    {
        return $this->confirm($command->intent, $command->entities);
    }

    public function confirm(?string $intent, array $entities = []): string
    {
        return match ($intent) {
            'create_ticket' => sprintf('Create ticket for %s about %s. Confirm?', $entities['customer_name'] ?? 'this customer', $entities['subject'] ?? 'this issue'),
            'create_job' => sprintf('Create job for %s at %s. Confirm?', $entities['customer_name'] ?? 'this customer', $entities['scheduled_for'] ?? 'the requested time'),
            'schedule_callback' => sprintf('Schedule callback for %s. Confirm?', $entities['scheduled_for'] ?? 'that time'),
            'list_tasks' => 'Read out your current tasks. Confirm?',
            'update_status' => sprintf('Update the status to %s. Confirm?', $entities['status'] ?? 'the requested value'),
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

    public function success(?string $intent, array $result): string
    {
        return $this->completed($result);
    }

    public function permissionDenied(?string $intent = null): string
    {
        return "I don't have permission to do that.";
    }
}
