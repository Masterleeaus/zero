<?php

namespace App\TitanCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * TitanCoreActivity — fired for every significant Titan Core action.
 *
 * Broadcast channel: titan.core.activity
 *
 * Required payload fields:
 *   intent, provider, duration, tokens, company_id, user_id, status
 */
class TitanCoreActivity
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $intent,
        public readonly string $provider,
        public readonly float $duration,
        public readonly int $tokens,
        public readonly ?int $companyId,
        public readonly ?int $userId,
        public readonly string $status,
        public readonly array $payload = [],
    ) {
    }

    /**
     * Build a TitanCoreActivity from a generic array payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            intent:    (string) ($data['intent']     ?? 'unknown'),
            provider:  (string) ($data['provider']   ?? 'unknown'),
            duration:  (float)  ($data['duration']   ?? 0.0),
            tokens:    (int)    ($data['tokens']      ?? 0),
            companyId: isset($data['company_id']) ? (int) $data['company_id'] : null,
            userId:    isset($data['user_id'])    ? (int) $data['user_id']    : null,
            status:    (string) ($data['status']     ?? 'unknown'),
            payload:   (array)  ($data['payload']    ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'intent'     => $this->intent,
            'provider'   => $this->provider,
            'duration'   => $this->duration,
            'tokens'     => $this->tokens,
            'company_id' => $this->companyId,
            'user_id'    => $this->userId,
            'status'     => $this->status,
            'payload'    => $this->payload,
            'timestamp'  => now()->toIso8601String(),
        ];
    }
}
