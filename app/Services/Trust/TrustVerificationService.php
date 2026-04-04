<?php

declare(strict_types=1);

namespace App\Services\Trust;

use App\Events\Trust\ChainTamperingDetected;
use App\Models\Trust\TrustLedgerEntry;
use Illuminate\Database\Eloquent\Model;

class TrustVerificationService
{
    public function __construct(protected TrustLedgerService $ledgerService) {}

    /**
     * Verify a single ledger entry by re-computing its hash.
     */
    public function verifyEntry(TrustLedgerEntry $entry): bool
    {
        return $this->ledgerService->verifyEntry($entry);
    }

    /**
     * Scan all entries for a company and return any whose hash does not match.
     * Fires ChainTamperingDetected if tampering is found.
     *
     * @return array<int, array<string, mixed>>  List of tampered entry summaries.
     */
    public function detectTampering(int $companyId): array
    {
        $tampered = [];

        TrustLedgerEntry::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->each(function (TrustLedgerEntry $entry) use (&$tampered) {
                if (! $this->verifyEntry($entry)) {
                    $tampered[] = [
                        'id'           => $entry->id,
                        'chain_hash'   => $entry->chain_hash,
                        'entry_type'   => $entry->entry_type,
                        'subject_type' => $entry->subject_type,
                        'subject_id'   => $entry->subject_id,
                    ];
                }
            });

        if (! empty($tampered)) {
            ChainTamperingDetected::dispatch($companyId, $tampered);
        }

        return $tampered;
    }

    /**
     * Generate a compliance proof document for a subject model.
     *
     * Returns a structured array containing every entry in the chain along
     * with their verification status — suitable for export or display.
     *
     * @return array<string, mixed>
     */
    public function generateComplianceProof(Model $subject): array
    {
        $entries = $this->ledgerService->getChain($subject);

        $proofEntries = $entries->map(function (TrustLedgerEntry $entry) {
            return [
                'id'           => $entry->id,
                'entry_type'   => $entry->entry_type,
                'chain_hash'   => $entry->chain_hash,
                'parent_hash'  => $entry->parent_hash,
                'actor_type'   => $entry->actor_type,
                'actor_id'     => $entry->actor_id,
                'signed_at'    => $entry->signed_at?->toIso8601String(),
                'payload'      => $entry->payload,
                'verified'     => $this->verifyEntry($entry),
                'attachments'  => $entry->attachments->map(fn ($a) => [
                    'file_name'       => $a->file_name,
                    'mime_type'       => $a->mime_type,
                    'checksum'        => $a->checksum,
                    'attachment_type' => $a->attachment_type,
                    'captured_at'     => $a->captured_at?->toIso8601String(),
                ])->all(),
            ];
        })->all();

        return [
            'subject_type'  => get_class($subject),
            'subject_id'    => $subject->getKey(),
            'entry_count'   => $entries->count(),
            'chain_intact'  => collect($proofEntries)->every(fn ($e) => $e['verified']),
            'generated_at'  => now()->toIso8601String(),
            'entries'       => $proofEntries,
        ];
    }
}
