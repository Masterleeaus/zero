<?php

namespace App\TitanCore\Zero\AI\Nexus;

class CritiqueLoopEngine
{
    public function run(array $votes): array
    {
        $summaries = array_map(static fn (array $vote) => [
            'core' => $vote['core'] ?? 'unknown',
            'summary' => $vote['summary'] ?? null,
            'confidence' => $vote['confidence'] ?? null,
        ], $votes);

        return array_map(static function (array $vote) use ($summaries) {
            $vote['criticisms'] = array_values(array_filter(array_map(static function (array $other) use ($vote) {
                if (($other['core'] ?? null) === ($vote['core'] ?? null)) {
                    return null;
                }
                return sprintf('Compare %s against %s output.', (string) ($vote['core'] ?? 'core'), (string) ($other['core'] ?? 'core'));
            }, $summaries)));

            return $vote;
        }, $votes);
    }
}
