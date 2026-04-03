<?php

namespace App\Titan\Core\Contracts;

/**
 * SignalContract — canonical interface for signal envelope normalisation.
 *
 * Aligns with:
 *   docs/titancore/14_SIGNAL_ENVELOPE_SPEC.md
 *   docs/titancore/27_SIGNAL_TO_AI_PROCESS_FLOW.md
 *   docs/titancore/29_AI_APPROVAL_GOVERNANCE_MODEL.md
 *
 * Normalises signal structure, AI-resolution eligibility, approval gating,
 * rewind references, and dispatch lifecycle hooks.
 *
 * Do NOT duplicate dispatcher logic. Bridge to SignalDispatcher / SignalsService.
 */
interface SignalContract
{
    /**
     * Normalise a raw signal payload into canonical envelope structure.
     *
     * Ensures: company_id tenancy, severity, kind, approval chain, rewind reference.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalise(array $payload): array;

    /**
     * Determine whether a signal is eligible for AI-resolution.
     *
     * Returns false if the signal is pending human approval or has been rejected.
     *
     * @param  array<string, mixed>  $signal
     */
    public function isAiEligible(array $signal): bool;

    /**
     * Return approval gating metadata for a signal.
     *
     * Includes: requires_approval, approval_chain, next_approver.
     *
     * @param  array<string, mixed>  $signal
     * @return array<string, mixed>
     */
    public function approvalGate(array $signal): array;

    /**
     * Attach rewind reference metadata to a signal.
     *
     * @param  array<string, mixed>  $signal
     * @return array<string, mixed>
     */
    public function withRewindRef(array $signal, string $rewindRef): array;

    /**
     * Dispatch a normalised signal through the canonical pipeline.
     *
     * Triggers: SignalDispatcher → PulseSubscriber → RewindSubscriber → ZeroSubscriber
     *
     * @param  array<string, mixed>  $signal
     * @return array<string, mixed>
     */
    public function dispatch(array $signal): array;

    /**
     * Record a signal dispatch event in the audit trail.
     *
     * @param  array<string, mixed>  $signal
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function recordDispatch(array $signal, array $meta = []): array;

    /**
     * Build a canonical envelope from a collection of signals.
     *
     * @param  array<int, array<string, mixed>>  $signals
     * @param  array<string, mixed>              $context
     * @return array<string, mixed>
     */
    public function envelope(array $signals, array $context = []): array;
}
