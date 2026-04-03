<?php

namespace App\TitanCore\Zero\AI\Context;

class InstructionBuilder
{
    /**
     * @param  array<string, mixed>  $envelope
     * @param  array<string, mixed>  $memory
     */
    public function build(array $envelope, array $memory = []): string
    {
        $summary = (string) ($envelope['summary'] ?? 'Titan Zero decision envelope');
        $headline = json_encode($envelope['headline'] ?? [], JSON_UNESCAPED_UNICODE);
        $signals = json_encode($envelope['top_signals'] ?? [], JSON_UNESCAPED_UNICODE);
        $memoryJson = json_encode($memory, JSON_UNESCAPED_UNICODE);

        return trim(implode("\n\n", [
            'You are Titan Zero, the governance-first AI kernel for Titan Core.',
            'Use the supplied signal envelope, state pressure, and memory to decide the next safe action.',
            "Envelope Summary: {$summary}",
            "Headline: {$headline}",
            "Top Signals: {$signals}",
            "Memory Snapshot: {$memoryJson}",
        ]));
    }
}
