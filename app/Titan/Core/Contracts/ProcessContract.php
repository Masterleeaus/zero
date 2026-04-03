<?php

namespace App\Titan\Core\Contracts;

/**
 * ProcessContract — defines the canonical interface for process lifecycle integration.
 *
 * Aligns with the lifecycle state machine defined in:
 *   docs/titancore/21_LIFECYCLE_ENGINE_STATE_MACHINE.md
 *   docs/titancore/41_PROCESS_ENGINE_OVERVIEW.md
 *
 * Do not override ProcessStateMachine or ProcessRecorder. This contract normalises
 * cross-domain access to the process layer.
 */
interface ProcessContract
{
    /**
     * Begin a new process and return the process record.
     *
     * @param  array<string, mixed>  $payload  Must include company_id, entity_type, domain
     * @return array<string, mixed>
     */
    public function begin(array $payload): array;

    /**
     * Transition a process to a new state.
     *
     * Valid transitions are defined in ProcessStateMachine::VALID.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function transition(string $processId, string $toState, array $metadata = []): array;

    /**
     * Determine whether the process is eligible for approval checkpoint.
     *
     * Returns true when current_state is 'awaiting-approval'.
     */
    public function requiresApproval(string $processId): bool;

    /**
     * Determine whether the process is eligible for rewind.
     *
     * Returns true when current_state is 'processed' or 'rewinding'.
     */
    public function isRewindEligible(string $processId): bool;

    /**
     * Return the full audit trail for a process.
     *
     * @return array<int, array<string, mixed>>
     */
    public function auditTrail(string $processId): array;

    /**
     * Return all valid state transitions from a given state.
     *
     * @return array<int, string>
     */
    public function validTransitions(string $fromState): array;

    /**
     * Return the current state of a process.
     */
    public function currentState(string $processId): string;

    /**
     * Link a process to a signal envelope (signal_id reference).
     *
     * @return array<string, mixed>
     */
    public function linkSignal(string $processId, string $signalId): array;

    /**
     * Link a process to a rewind snapshot.
     *
     * @return array<string, mixed>
     */
    public function linkRewind(string $processId, string $rewindRef): array;
}
