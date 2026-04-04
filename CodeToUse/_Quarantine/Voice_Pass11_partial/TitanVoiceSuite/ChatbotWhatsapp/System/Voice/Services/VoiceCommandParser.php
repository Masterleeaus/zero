<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class VoiceCommandParser
{
    public function parse(string $transcript): VoiceCommand
    {
        $clean = trim((string) mb_strtolower($transcript));

        $entities = array_filter([
            'customer_name' => $this->extract('/(?:for|customer)\s+([a-z0-9 ._-]+)/i', $transcript),
            'subject' => $this->extract('/(?:about|regarding)\s+(.+)/i', $transcript),
            'priority' => $this->extract('/\b(high|urgent|medium|normal|low)\b/i', $transcript),
            'scheduled_for' => $this->extract('/\b(tomorrow|today|next\s+\w+|at\s+\d{1,2}(?::\d{2})?\s?(?:am|pm)?)\b/i', $transcript),
            'technician_name' => $this->extract('/(?:assign to|technician|tech)\s+([a-z0-9 ._-]+)/i', $transcript),
            'job_reference' => $this->extract('/(?:job|ticket|quote|invoice)\s+#?([a-z0-9_-]+)/i', $transcript),
            'status' => $this->extract('/(?:mark as|set status to|status)\s+([a-z0-9 _-]+)/i', $transcript),
        ], static fn ($value) => $value !== null && $value !== '');

        $intentMap = [
            'create_ticket' => ['create ticket', 'new ticket', 'open ticket'],
            'create_job' => ['create job', 'new job', 'book job'],
            'list_tasks' => ['show my tasks', 'list tasks', 'what are my tasks'],
            'schedule_callback' => ['schedule callback', 'book callback', 'call back'],
            'update_status' => ['mark as', 'update status', 'set status'],
            'create_quote' => ['create quote', 'new quote', 'quote for'],
            'create_invoice' => ['create invoice', 'new invoice', 'invoice for'],
            'assign_technician' => ['assign technician', 'assign to', 'send technician'],
            'close_job' => ['close job', 'complete job', 'finish job'],
            'update_schedule' => ['reschedule', 'move booking', 'change schedule', 'update schedule'],
        ];

        $intent = 'unknown';
        $confidence = 0.35;

        foreach ($intentMap as $candidate => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($clean, $pattern)) {
                    $intent = $candidate;
                    $confidence = 0.72;
                    break 2;
                }
            }
        }

        if ($intent === 'create_ticket' && isset($entities['customer_name'], $entities['subject'])) {
            $confidence = 0.95;
        } elseif ($intent === 'create_job' && isset($entities['customer_name'])) {
            $confidence = isset($entities['scheduled_for']) ? 0.94 : 0.88;
        } elseif ($intent === 'list_tasks') {
            $confidence = 0.92;
        } elseif ($intent === 'schedule_callback' && isset($entities['scheduled_for'])) {
            $confidence = 0.91;
        } elseif ($intent === 'update_status' && (isset($entities['status']) || isset($entities['subject']))) {
            $confidence = 0.83;
        } elseif ($intent === 'create_quote' && isset($entities['customer_name'])) {
            $confidence = isset($entities['subject']) ? 0.93 : 0.86;
        } elseif ($intent === 'create_invoice' && isset($entities['customer_name'])) {
            $confidence = 0.90;
        } elseif ($intent === 'assign_technician' && isset($entities['technician_name'])) {
            $confidence = isset($entities['job_reference']) ? 0.93 : 0.84;
        } elseif ($intent === 'close_job' && isset($entities['job_reference'])) {
            $confidence = 0.91;
        } elseif ($intent === 'update_schedule' && isset($entities['scheduled_for'])) {
            $confidence = isset($entities['customer_name']) ? 0.90 : 0.81;
        }

        $missing = $this->missingFor($intent, $entities);

        return new VoiceCommand($intent, $entities, $confidence, $missing, $transcript);
    }

    private function extract(string $pattern, string $transcript): ?string
    {
        if (preg_match($pattern, $transcript, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) ?: null;
        }

        return null;
    }

    private function missingFor(string $intent, array $entities): array
    {
        return match ($intent) {
            'create_ticket' => $this->missing(['customer_name', 'subject'], $entities),
            'create_job' => $this->missing(['customer_name'], $entities),
            'schedule_callback' => $this->missing(['scheduled_for'], $entities),
            'create_quote' => $this->missing(['customer_name'], $entities),
            'create_invoice' => $this->missing(['customer_name'], $entities),
            'assign_technician' => $this->missing(['technician_name', 'job_reference'], $entities),
            'close_job' => $this->missing(['job_reference'], $entities),
            'update_schedule' => $this->missing(['scheduled_for'], $entities),
            default => [],
        };
    }

    private function missing(array $required, array $entities): array
    {
        return array_values(array_filter($required, static fn (string $field): bool => empty($entities[$field])));
    }
}
