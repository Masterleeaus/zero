<?php

namespace App\Jobs\TitanPwa;

use App\Models\TzPwaSignalIngress;
use App\Titan\Signals\SignalsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Promotes an accepted PWA ingress record into the canonical Titan Signal pipeline.
 *
 * Flow:
 *   TzPwaSignalIngress (accepted) → SignalsService::recordAndIngest → tz_signals + tz_processes
 *
 * Idempotent: if the ingress row already has a promoted_to_event_id, the job exits immediately.
 */
class PromotePwaIngressJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly int $ingressId,
    ) {
        $this->onQueue('titan-signals');
    }

    public function handle(SignalsService $signalsService): void
    {
        /** @var TzPwaSignalIngress|null $ingress */
        $ingress = TzPwaSignalIngress::find($this->ingressId);

        if (! $ingress) {
            Log::warning('[PromotePwaIngressJob] Ingress not found', ['ingress_id' => $this->ingressId]);

            return;
        }

        // Idempotency guard: already promoted
        if ($ingress->promoted_to_event_id !== null) {
            return;
        }

        // Only promote consensus-passed ingress
        if (! $ingress->consensus_passed) {
            return;
        }

        try {
            $result = $signalsService->recordAndIngest(
                [
                    'company_id'       => $ingress->company_id,
                    'user_id'          => $ingress->user_id,
                    'entity_type'      => 'pwa_signal',
                    'domain'           => 'pwa',
                    'originating_node' => 'pwa-node:'.$ingress->node_id,
                    'data'             => $ingress->payload ?? [],
                ],
                [
                    'type'     => $ingress->signal_key,
                    'kind'     => 'pwa_ingress',
                    'severity' => $ingress->meta['severity'] ?? 'GREEN',
                    'status'   => 'pending',
                    'payload'  => $ingress->payload ?? [],
                    'meta'     => array_merge($ingress->meta ?? [], [
                        'pwa_ingress_id' => $ingress->id,
                        'node_id'        => $ingress->node_id,
                        'signature'      => $ingress->signature,
                        'consensus_score' => $ingress->consensus_score,
                    ]),
                ]
            );

            $signalId = $result['signal']['id'] ?? null;

            $ingress->update([
                'signal_stage'         => 'promoted',
                // Store the canonical signal ID string in meta for linkage
                // (promoted_to_event_id is an integer column; store signal ID in meta)
                'processed_at'         => now(),
                'meta'                 => array_merge($ingress->meta ?? [], [
                    'promoted_signal_id' => $signalId,
                    'promoted_at'        => now()->toIso8601String(),
                ]),
            ]);

            // Update device last_success_at
            \App\Models\TzPwaDevice::where('node_id', $ingress->node_id)
                ->where('company_id', $ingress->company_id)
                ->update(['last_success_at' => now()]);

            Log::info('[PromotePwaIngressJob] Ingress promoted', [
                'ingress_id' => $ingress->id,
                'signal_id'  => $signalId,
            ]);
        } catch (Throwable $e) {
            $ingress->update([
                'failure_reason' => substr($e->getMessage(), 0, 500),
            ]);

            Log::error('[PromotePwaIngressJob] Promotion failed', [
                'ingress_id' => $ingress->id,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('[PromotePwaIngressJob] Job permanently failed', [
            'ingress_id' => $this->ingressId,
            'error'      => $e->getMessage(),
        ]);

        TzPwaSignalIngress::where('id', $this->ingressId)->update([
            'signal_stage'    => 'failed',
            'failure_reason'  => substr($e->getMessage(), 0, 500),
            'last_error_code' => 'promotion_failed',
            'retry_count'     => \Illuminate\Support\Facades\DB::raw('retry_count + 1'),
        ]);
    }
}
