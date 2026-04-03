<?php

namespace Extensions\TitanHello\Services;

use Extensions\TitanHello\Models\TitanHelloCallSession;
use Extensions\TitanHello\Models\ExtVoicechatbotHistory;

class CallSummaryService
{
    /**
     * Rule-based summary to keep it deterministic.
     * Can be replaced with a structured LLM summariser later.
     */
    public function finalise(TitanHelloCallSession $session): void
    {
        if ($session->summary) {
            return;
        }

        $text = $this->getTranscript($session, 60);
        if ($text === '') {
            return;
        }

        $lines = preg_split('/\r?\n/', $text) ?: [];
        $userBits = [];
        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln === '') continue;
            $userBits[] = $ln;
            if (count($userBits) >= 6) break;
        }

        $parts = [];
        if ($session->job_type) $parts[] = "Job: {$session->job_type}";
        if ($session->suburb) $parts[] = "Area: {$session->suburb}";
        if ($session->urgency) $parts[] = "Urgency: {$session->urgency}";
        $first = implode(' | ', $parts);

        $snippet = implode(' / ', array_slice($userBits, 0, 3));
        $summary = trim($first . ($snippet ? " — {$snippet}" : ''));

        $session->summary = $summary !== '' ? $summary : null;
        $session->save();
    }

    private function getTranscript(TitanHelloCallSession $session, int $limit): string
    {
        if (!$session->conversation_db_id) {
            return '';
        }

        return ExtVoicechatbotHistory::query()
            ->where('conversation_id', $session->conversation_db_id)
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn($row) => trim((string) $row->message))
            ->filter()
            ->implode("\n");
    }
}
