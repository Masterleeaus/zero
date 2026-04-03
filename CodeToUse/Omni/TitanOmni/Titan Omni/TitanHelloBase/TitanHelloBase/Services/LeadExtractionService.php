<?php

namespace Extensions\TitanHello\Services;

use Extensions\TitanHello\Models\TitanHelloCallSession;
use Extensions\TitanHello\Models\TitanHelloLead;
use Extensions\TitanHello\Models\ExtVoicechatbotHistory;

/**
 * Rule-based extraction (no LLM) to keep this extension shippable.
 * Can be upgraded later to Titan Zero style structured extraction.
 */
class LeadExtractionService
{
    /**
     * Attempt to extract/update lead fields from the most recent transcript text.
     * Safe to call frequently.
     */
    public function extractAndUpdate(TitanHelloCallSession $session): void
    {
        $text = $this->getRecentTranscript($session, 20);
        if ($text === '') {
            return;
        }

        $updates = [];

        // Name heuristics
        if (!$session->caller_name) {
            if (preg_match('/\b(?:my name is|this is|it\'s)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\b/i', $text, $m)) {
                $updates['caller_name'] = trim($m[1]);
            }
        }

        // Suburb / location heuristics (very rough)
        if (!$session->suburb) {
            if (preg_match('/\b(?:i\s*(?:am|\'m)\s+in|located in|in the area of|in)\s+([A-Za-z\s]{3,40})\b/i', $text, $m)) {
                $cand = trim($m[1]);
                $cand = preg_replace('/\s{2,}/', ' ', $cand);
                // Avoid capturing generic words
                if (!preg_match('/\b(today|tomorrow|now|urgent|asap)\b/i', $cand)) {
                    $updates['suburb'] = $cand;
                }
            }
        }

        // Job type heuristics
        if (!$session->job_type) {
            $map = [
                'plumbing' => ['plumb', 'tap', 'toilet', 'leak', 'blocked', 'hot water', 'drain'],
                'electrical' => ['electric', 'power', 'switch', 'outlet', 'light', 'circuit', 'sparks'],
                'roofing' => ['roof', 'gutter', 'leak roof', 'downpipe'],
                'tiling' => ['tile', 'tiling', 'grout'],
                'waterproofing' => ['waterproof', 'membrane', 'shower', 'bathroom leak'],
                'hvac' => ['aircon', 'air con', 'split system', 'heater'],
                'general' => ['quote', 'job', 'repair', 'fix']
            ];
            $lower = mb_strtolower($text);
            foreach ($map as $type => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($lower, $kw)) {
                        $updates['job_type'] = $type;
                        break 2;
                    }
                }
            }
        }

        // Urgency heuristics
        if (!$session->urgency) {
            if (preg_match('/\b(urgent|asap|emergency|right now|today)\b/i', $text)) {
                $updates['urgency'] = 'urgent';
            } elseif (preg_match('/\b(tomorrow|this week|soon)\b/i', $text)) {
                $updates['urgency'] = 'normal';
            }
        }

        if (!empty($updates)) {
            $session->fill($updates);
            $session->save();
        }

        // Upsert a lead row if we have at least job_type or suburb.
        if ($session->job_type || $session->suburb || $session->caller_name) {
            TitanHelloLead::query()->updateOrCreate(
                ['call_session_id' => $session->id],
                [
                    'agent_id' => $session->agent_id,
                    'from_number' => $session->from_number,
                    'caller_name' => $session->caller_name,
                    'job_type' => $session->job_type,
                    'suburb' => $session->suburb,
                    'urgency' => $session->urgency,
                    'notes' => null,
                ]
            );
        }
    }

    private function getRecentTranscript(TitanHelloCallSession $session, int $limit): string
    {
        if (!$session->conversation_db_id) {
            return '';
        }

        $msgs = ExtVoicechatbotHistory::query()
            ->where('conversation_id', $session->conversation_db_id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($row) {
                return trim((string) $row->message);
            })
            ->filter()
            ->implode("\n");

        return (string) $msgs;
    }
}
