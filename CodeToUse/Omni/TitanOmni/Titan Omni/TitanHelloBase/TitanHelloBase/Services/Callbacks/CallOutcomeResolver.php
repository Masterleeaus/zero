<?php

namespace Modules\TitanHello\Services\Callbacks;

use Modules\TitanHello\Models\Call;

class CallOutcomeResolver
{
    /**
     * Map provider call status into a canonical outcome.
     * Keep it conservative: if uncertain, don't overwrite a more specific outcome.
     */
    public function resolveOutcome(?string $providerStatus): ?string
    {
        $s = strtolower((string) $providerStatus);

        return match ($s) {
            'completed' => 'answered',
            'answered'  => 'answered',
            'no-answer', 'busy' => 'missed',
            'failed', 'canceled', 'cancelled' => 'failed',
            default => null,
        };
    }

    public function apply(Call $call, ?string $providerStatus): bool
    {
        $outcome = $this->resolveOutcome($providerStatus);
        if (!$outcome) return false;

        // Do not downgrade an outcome
        $current = (string)($call->call_outcome ?? '');
        if (in_array($current, ['answered'], true) && $outcome !== 'answered') {
            return false;
        }

        $call->call_outcome = $outcome;

        if ($outcome === 'answered' && !$call->answered_at) {
            $call->answered_at = now();
        }

        if (in_array($outcome, ['missed','failed'], true) && !$call->ended_at) {
            $call->ended_at = now();
        }

        $call->save();

        return true;
    }
}
