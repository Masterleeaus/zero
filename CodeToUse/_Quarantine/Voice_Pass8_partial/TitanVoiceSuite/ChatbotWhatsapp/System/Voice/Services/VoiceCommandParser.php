<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

use App\Extensions\ChatbotWhatsapp\System\Voice\DTO\VoiceCommand;

class VoiceCommandParser
{
    public function parse(string $transcript): VoiceCommand
    {
        $clean = trim(mb_strtolower($transcript));
        $entities = [
            'customer_name' => $this->extract('/(?:for|customer)\s+([a-z0-9 ._-]+)/i', $transcript),
            'subject' => $this->extract('/(?:about|regarding)\s+(.+)/i', $transcript),
            'priority' => $this->extract('/\b(high|urgent|medium|normal|low)\b/i', $transcript),
            'status' => $this->extract('/\b(open|pending|closed|done|resolved)\b/i', $transcript),
            'scheduled_for' => $this->extract('/\b(tomorrow|today|next\s+\w+|at\s+\d{1,2}(?::\d{2})?\s?(?:am|pm)?)\b/i', $transcript),
        ];

        $intentMap = [
            'create_ticket' => ['create ticket', 'new ticket', 'open ticket'],
            'create_job' => ['create job', 'new job', 'book job'],
            'list_tasks' => ['show my tasks', 'list tasks', 'what are my tasks'],
            'schedule_callback' => ['schedule callback', 'book callback', 'call back'],
            'update_status' => ['mark as', 'update status', 'set status', 'mark resolved'],
            'close_ticket' => ['close ticket', 'resolve ticket'],
            'view_report' => ['show report', 'view report', 'generate report'],
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

        if ($intent === 'create_ticket' && $entities['customer_name'] && $entities['subject']) {
            $confidence = 0.95;
        } elseif ($intent === 'create_job' && ($entities['customer_name'] || $entities['scheduled_for'])) {
            $confidence = 0.90;
        } elseif ($intent === 'list_tasks') {
            $confidence = 0.92;
        } elseif ($intent === 'schedule_callback' && $entities['scheduled_for']) {
            $confidence = 0.91;
        } elseif (in_array($intent, ['update_status', 'close_ticket'], true) && $entities['status']) {
            $confidence = 0.88;
        } elseif ($intent === 'view_report') {
            $confidence = 0.86;
        }

        $missing = $this->missingFor($intent, $entities);

        return new VoiceCommand($intent, array_filter($entities, fn ($v) => $v !== null && $v !== ''), $confidence, $missing, $transcript);
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
            'create_ticket' => array_values(array_filter(['customer_name', 'subject'], fn ($field) => empty($entities[$field]))),
            'create_job' => array_values(array_filter(['customer_name'], fn ($field) => empty($entities[$field]))),
            'schedule_callback' => array_values(array_filter(['scheduled_for'], fn ($field) => empty($entities[$field]))),
            'update_status', 'close_ticket' => array_values(array_filter(['status'], fn ($field) => empty($entities[$field]))),
            default => [],
        };
    }
}
