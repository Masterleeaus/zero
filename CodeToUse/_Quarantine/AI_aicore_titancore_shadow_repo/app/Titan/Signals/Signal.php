<?php

namespace App\Titan\Signals;

final class Signal
{
    public function __construct(
        public string $id,
        public string $type,
        public string $kind,
        public string $severity,
        public string $title,
        public ?string $body = null,
        public ?int $companyId = null,
        public ?int $teamId = null,
        public ?int $userId = null,
        public ?string $processId = null,
        public array $payload = [],
        public array $meta = [],
        public ?string $source = null,
        public ?string $origin = null,
        public ?string $status = 'new',
        public ?string $validationResult = null,
        public array $validationErrors = [],
        public array $validationWarnings = [],
        public array $approvalChain = [],
        public ?int $nextApproverId = null,
        public array $approvedBy = [],
        public ?string $processedEntityId = null,
        public ?string $processedAt = null,
        public ?string $rewindFrom = null,
        public ?string $rolledBackBy = null,
        public ?string $timestamp = null,
        public ?string $sourceEngine = null,
    ) {
        $this->timestamp ??= now()->toIso8601String();
    }

    public static function make(array $attributes): self
    {
        return new self(
            id: (string) ($attributes['id'] ?? ('sig-'.str_replace('.', '-', uniqid('', true)))),
            type: (string) ($attributes['type'] ?? 'generic'),
            kind: (string) ($attributes['kind'] ?? 'generic'),
            severity: (string) ($attributes['severity'] ?? SignalSeverity::AMBER),
            title: (string) ($attributes['title'] ?? 'Signal'),
            body: $attributes['body'] ?? null,
            companyId: isset($attributes['company_id']) ? (int) $attributes['company_id'] : null,
            teamId: isset($attributes['team_id']) ? (int) $attributes['team_id'] : null,
            userId: isset($attributes['user_id']) ? (int) $attributes['user_id'] : null,
            processId: $attributes['process_id'] ?? null,
            payload: (array) ($attributes['payload'] ?? []),
            meta: (array) ($attributes['meta'] ?? []),
            source: $attributes['source'] ?? null,
            origin: $attributes['origin'] ?? null,
            status: $attributes['status'] ?? 'new',
            validationResult: $attributes['validation_result'] ?? null,
            validationErrors: (array) ($attributes['validation_errors'] ?? []),
            validationWarnings: (array) ($attributes['validation_warnings'] ?? []),
            approvalChain: (array) ($attributes['approval_chain'] ?? []),
            nextApproverId: isset($attributes['next_approver_id']) ? (int) $attributes['next_approver_id'] : null,
            approvedBy: (array) ($attributes['approved_by'] ?? []),
            processedEntityId: isset($attributes['processed_entity_id']) ? (string) $attributes['processed_entity_id'] : null,
            processedAt: $attributes['processed_at'] ?? null,
            rewindFrom: $attributes['rewind_from'] ?? null,
            rolledBackBy: $attributes['rolled_back_by'] ?? null,
            timestamp: $attributes['timestamp'] ?? null,
            sourceEngine: $attributes['source_engine'] ?? $attributes['sourceEngine'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'kind' => $this->kind,
            'severity' => $this->severity,
            'title' => $this->title,
            'body' => $this->body,
            'company_id' => $this->companyId,
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'process_id' => $this->processId,
            'payload' => $this->payload,
            'meta' => $this->meta,
            'source' => $this->source,
            'origin' => $this->origin,
            'status' => $this->status,
            'validation_result' => $this->validationResult,
            'validation_errors' => $this->validationErrors,
            'validation_warnings' => $this->validationWarnings,
            'approval_chain' => $this->approvalChain,
            'next_approver_id' => $this->nextApproverId,
            'approved_by' => $this->approvedBy,
            'processed_entity_id' => $this->processedEntityId,
            'processed_at' => $this->processedAt,
            'rewind_from' => $this->rewindFrom,
            'rolled_back_by' => $this->rolledBackBy,
            'timestamp' => $this->timestamp,
            'source_engine' => $this->sourceEngine,
        ];
    }
}
