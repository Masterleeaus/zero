<?php

namespace App\Titan\Signals\Subscribers;

use App\Extensions\TitanRewind\System\Services\RewindEngine;
use App\Titan\Signals\SignalSubscriberInterface;
use Illuminate\Support\Facades\Log;

class RewindSubscriber implements SignalSubscriberInterface
{
    public function name(): string
    {
        return 'rewind';
    }

    public function handle(array $signal): array
    {
        $triggerTypes = config('titan-rewind.signal_integration.rewind_trigger_types', [
            'process.rewind.requested',
            'process.rollback.requested',
        ]);

        $accepted = in_array($signal['type'] ?? '', $triggerTypes, true);

        if ($accepted && !empty($signal['company_id']) && !empty($signal['process_id'])) {
            try {
                $payload = is_array($signal['payload'] ?? null) ? $signal['payload'] : [];

                app(RewindEngine::class)->initiate([
                    'company_id'  => (int) $signal['company_id'],
                    'team_id'     => isset($signal['team_id']) ? (int) $signal['team_id'] : null,
                    'user_id'     => isset($signal['user_id']) ? (int) $signal['user_id'] : null,
                    'actor_id'    => isset($signal['user_id']) ? (int) $signal['user_id'] : null,
                    'actor_type'  => 'signal',
                    'process_id'  => $signal['process_id'],
                    'entity_type' => $payload['entity_type'] ?? ($signal['meta']['entity_type'] ?? null),
                    'entity_id'   => $payload['entity_id'] ?? ($signal['meta']['entity_id'] ?? null),
                    'reason'      => $payload['reason'] ?? ('Signal-triggered rewind: ' . ($signal['type'] ?? 'unknown')),
                    'severity'    => $signal['severity'] ?? 'high',
                    'source_type' => 'signal',
                    'source_id'   => $signal['id'] ?? null,
                    'title'       => 'Rewind from signal: ' . ($signal['type'] ?? 'unknown'),
                ]);
            } catch (\Throwable $e) {
                Log::error('RewindSubscriber: engine initiation failed', [
                    'signal_id' => $signal['id'] ?? null,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return [
            'accepted'  => $accepted,
            'anchor'    => [
                'process_id' => $signal['process_id'] ?? null,
                'signal_id'  => $signal['id'] ?? null,
                'rewind_from' => $signal['rewind_from'] ?? null,
            ],
        ];
    }
}
