<?php

namespace App\Titan\Signals;

use App\Contracts\TitanIntegration\ZeroSignalBridgeContract;
use App\Titan\Signals\Providers\MoneySignalsProvider;
use App\Titan\Signals\Providers\WorkSignalsProvider;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SignalsService implements ZeroSignalBridgeContract
{
    /** @var SignalProviderInterface[] */
    private array $providers;

    public function __construct(
        private readonly SignalNormalizer $normalizer = new SignalNormalizer(),
        private readonly SignalRegistry $registry = new SignalRegistry(),
        private readonly SignalValidator $validator = new SignalValidator(),
        private readonly ApprovalChain $approvalChain = new ApprovalChain(),
        private readonly AuditTrail $auditTrail = new AuditTrail(),
        private readonly SignalDispatcher $dispatcher = new SignalDispatcher(),
        private readonly ProcessStateMachine $stateMachine = new ProcessStateMachine(),
        private readonly ProcessRecorder $processRecorder = new ProcessRecorder(),
        private readonly SignalPriorityEngine $priorityEngine = new SignalPriorityEngine(),
    ) {
        $this->providers = [
            new WorkSignalsProvider(),
            new MoneySignalsProvider(),
        ];
    }

    public function all(int $companyId, ?int $teamId = null, ?int $userId = null): array
    {
        $stored = $this->feed($companyId, array_filter([
            'team_id' => $teamId,
            'user_id' => $userId,
            'limit' => 100,
        ], fn ($value) => $value !== null));

        if ($stored) {
            return $stored;
        }

        $signals = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->getSignals($companyId, $teamId, $userId) as $signal) {
                $signals[] = $this->ingest($signal->toArray());
            }
        }

        return $signals;
    }

    public function publish(array $signals): array
    {
        $published = [];
        foreach ($signals as $payload) {
            $published[] = $this->ingest((array) $payload);
        }

        return $published;
    }

    public function recordAndIngest(array $processPayload, array $signalPayload = []): array
    {
        $process = $this->processRecorder->record($processPayload);
        $signal = $this->ingest(array_merge($signalPayload, [
            'company_id' => $processPayload['company_id'] ?? null,
            'team_id' => $processPayload['team_id'] ?? null,
            'user_id' => $processPayload['user_id'] ?? null,
            'process_id' => $process['process_id'],
            'kind' => $signalPayload['kind'] ?? $this->registry->defaultKind(($signalPayload['type'] ?? (($processPayload['entity_type'] ?? 'entity').'.created')), ($processPayload['entity_type'] ?? 'generic')),
            'type' => $signalPayload['type'] ?? (($processPayload['entity_type'] ?? 'entity').'.created'),
            'payload' => $signalPayload['payload'] ?? ($processPayload['data'] ?? []),
            'meta' => array_merge($signalPayload['meta'] ?? [], [
                'originating_node' => $processPayload['originating_node'] ?? 'server',
                'process_domain' => $processPayload['domain'] ?? 'general',
            ]),
        ]));

        return [
            'process' => $process,
            'signal' => $signal,
        ];
    }

    public function ingest(array $payload): array
    {
        $normalized = $this->normalizer->normalize($payload);

        if (empty($normalized['company_id'])) {
            throw new InvalidArgumentException('company_id is required to ingest a signal.');
        }

        if (! empty($normalized['process_id'])) {
            $this->safeTransition($normalized['process_id'], 'awaiting-validation', ['stage' => 'signal_ingest']);
        }

        $validation = $this->validator->validate($normalized);
        $normalized['validation_result'] = $validation['result'];
        $normalized['validation_errors'] = $validation['errors'];
        $normalized['validation_warnings'] = $validation['warnings'];
        $normalized['status'] = $validation['result'] === 'REJECTED' ? 'validation-rejected' : 'validated';

        $approval = $this->approvalChain->determine($normalized);
        $normalized['approval_chain'] = $approval['approval_chain'];
        $normalized['meta'] = array_merge($normalized['meta'] ?? [], [
            'next_approver' => $approval['next_approver'],
            'requires_approval' => $approval['requires_approval'],
        ]);
        $normalized['priority'] = $this->priorityEngine->score($normalized);

        if (! empty($normalized['process_id'])) {
            $this->auditTrail->recordEntry(
                $normalized['process_id'],
                'signal.validated',
                [
                    'validation_result' => $validation['result'],
                    'warnings' => $validation['warnings'],
                    'errors' => $validation['errors'],
                ],
                $normalized['id'],
                $normalized['user_id']
            );
        }

        $this->store($normalized);
        $this->queue($normalized);

        if (! empty($normalized['process_id'])) {
            if ($validation['result'] === 'REJECTED') {
                $this->safeTransition($normalized['process_id'], 'validation-rejected', ['signal_id' => $normalized['id']]);
            } else {
                $this->safeTransition($normalized['process_id'], 'validation-approved', ['signal_id' => $normalized['id']]);
                $this->safeTransition($normalized['process_id'], 'awaiting-processing', ['signal_id' => $normalized['id']]);
                if ($approval['requires_approval']) {
                    $this->approvalChain->queue($normalized);
                } else {
                    $dispatch = $this->dispatcher->dispatch($normalized);
                    DB::table('tz_signal_queue')->where('signal_id', $normalized['id'])->update([
                        'broadcast_status' => 'sent',
                        'broadcast_at' => now(),
                    ]);
                    $normalized['dispatch'] = $dispatch;
                    $this->safeTransition($normalized['process_id'], 'processing', ['signal_id' => $normalized['id'], 'dispatched' => true]);
                }
            }
        }

        return $normalized;
    }

    public function store(array $signal): void
    {
        DB::table('tz_signals')->updateOrInsert(
            ['id' => $signal['id']],
            [
                'company_id' => $signal['company_id'],
                'team_id' => $signal['team_id'],
                'user_id' => $signal['user_id'],
                'process_id' => $signal['process_id'] ?? null,
                'type' => $signal['type'],
                'kind' => $signal['kind'],
                'severity' => $signal['severity'],
                'source' => $signal['source'],
                'origin' => $signal['origin'],
                'status' => $signal['status'] ?? 'stored',
                'validation_result' => $signal['validation_result'] ?? null,
                'payload' => json_encode($signal['payload'] ?? [], JSON_UNESCAPED_UNICODE),
                'meta' => json_encode(array_merge($signal['meta'] ?? [], ['priority' => $signal['priority'] ?? null]), JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function queue(array $signal): void
    {
        DB::table('tz_signal_queue')->updateOrInsert(
            ['signal_id' => $signal['id']],
            [
                'payload' => json_encode($signal, JSON_UNESCAPED_UNICODE),
                'broadcast_status' => 'pending',
                'retry_count' => 0,
                'created_at' => now(),
            ]
        );
    }

    public function dispatchPending(int $limit = 50): array
    {
        return $this->dispatcher->flushPending($limit);
    }

    public function feed(int $companyId, array $filters = []): array
    {
        $query = DB::table('tz_signals')->where('company_id', $companyId)->orderByDesc('created_at');

        foreach (['team_id', 'user_id', 'type', 'severity', 'status', 'process_id'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $items = $query->limit((int) ($filters['limit'] ?? 50))->get()->map(function ($row) {
            $item = (array) $row;
            $item['payload'] = json_decode($item['payload'] ?? '[]', true) ?: [];
            $item['meta'] = json_decode($item['meta'] ?? '[]', true) ?: [];
            $item['priority'] = $item['meta']['priority'] ?? $this->priorityEngine->score($item);
            return $item;
        })->all();

        return ! empty($filters['ranked']) ? $this->priorityEngine->rank($items) : $items;
    }

    public function timeline(string $processId): array
    {
        return $this->auditTrail->timeline($processId);
    }

    public function approvals(int $companyId, array $filters = []): array
    {
        return $this->approvalChain->pending($companyId, $filters);
    }

    public function approvalDecision(string $processId, string $decision, ?string $actor = null, array $meta = []): array
    {
        return $this->approvalChain->decide($processId, $decision, $actor, $meta);
    }

    public function process(string $processId): array
    {
        $process = DB::table('tz_processes')->where('id', $processId)->first();
        if (! $process) {
            throw new InvalidArgumentException('Unknown process.');
        }

        return [
            'process' => [
                'id' => $process->id,
                'company_id' => $process->company_id,
                'team_id' => $process->team_id,
                'user_id' => $process->user_id,
                'entity_type' => $process->entity_type,
                'domain' => $process->domain,
                'originating_node' => $process->originating_node,
                'current_state' => $process->current_state,
                'data' => json_decode($process->data ?? '[]', true) ?: [],
                'context' => json_decode($process->context ?? '[]', true) ?: [],
                'created_at' => $process->created_at,
                'updated_at' => $process->updated_at,
            ],
            'states' => DB::table('tz_process_states')->where('process_id', $processId)->orderBy('created_at')->get()->map(function ($row) {
                $item = (array) $row;
                $item['metadata'] = json_decode($item['metadata'] ?? '[]', true) ?: [];
                return $item;
            })->all(),
            'signals' => $this->feed((int) $process->company_id, ['process_id' => $processId, 'limit' => 100, 'ranked' => true]),
            'audit' => $this->timeline($processId),
            'approvals' => DB::table('tz_approval_queue')->where('process_id', $processId)->get()->map(function ($row) {
                $item = (array) $row;
                $item['approval_chain'] = json_decode($item['approval_chain'] ?? '[]', true) ?: [];
                $item['approved_by'] = json_decode($item['approved_by'] ?? '[]', true) ?: [];
                $item['decision_meta'] = json_decode($item['decision_meta'] ?? '[]', true) ?: [];
                return $item;
            })->all(),
        ];
    }

    public function registry(): array
    {
        return config('titan_signal.registry', []);
    }

    public function envelope(int $companyId, ?int $teamId = null, ?int $userId = null): array
    {
        return app(EnvelopeBuilder::class)->build([
            'company_id' => $companyId,
            'team_id' => $teamId,
            'user_id' => $userId,
            'signals' => $this->feed($companyId, array_filter([
                'team_id' => $teamId,
                'user_id' => $userId,
                'limit' => 100,
                'ranked' => true,
            ], fn ($value) => $value !== null)),
            'summary' => 'Titan Signal envelope',
        ]);
    }

    private function safeTransition(string $processId, string $state, array $metadata = []): void
    {
        $process = DB::table('tz_processes')->where('id', $processId)->first();
        if (! $process || $process->current_state === $state) {
            return;
        }

        $allowed = $this->stateMachine->transitions()[(string) $process->current_state] ?? [];
        if (in_array($state, $allowed, true)) {
            $this->stateMachine->transitionState($processId, $state, $metadata);
        }
    }
}
