<?php

declare(strict_types=1);

namespace App\Services\Trust;

use App\Events\Trust\ChainSealed;
use App\Events\Trust\LedgerEntryRecorded;
use App\Models\Trust\TrustChainSeal;
use App\Models\Trust\TrustEvidenceAttachment;
use App\Models\Trust\TrustLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrustLedgerService
{
    /**
     * Record a new ledger entry, wrapped in a locked transaction to prevent
     * race conditions when resolving the latest parent_hash.
     */
    public function record(
        string $entryType,
        Model $subject,
        array $payload,
        ?User $actor = null,
    ): TrustLedgerEntry {
        return DB::transaction(function () use ($entryType, $subject, $payload, $actor) {
            // Lock the latest entry for this company to serialise parent_hash resolution.
            $companyId = $subject->company_id ?? ($actor?->company_id ?? 0);

            $latest = TrustLedgerEntry::query()
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $parentHash = $latest?->chain_hash;
            $signedAt   = now();
            $actorType  = $actor ? 'user' : 'system';
            $actorId    = $actor?->id;

            $chainHash = $this->buildChainHash(
                $parentHash,
                $entryType,
                get_class($subject),
                (string) $subject->getKey(),
                (string) ($actorId ?? ''),
                $payload,
                $signedAt->toIso8601String(),
            );

            $entry = TrustLedgerEntry::create([
                'company_id'   => $companyId,
                'chain_hash'   => $chainHash,
                'parent_hash'  => $parentHash,
                'entry_type'   => $entryType,
                'subject_type' => get_class($subject),
                'subject_id'   => $subject->getKey(),
                'actor_type'   => $actorType,
                'actor_id'     => $actorId,
                'payload'      => $payload,
                'signed_at'    => $signedAt,
                'created_at'   => $signedAt,
            ]);

            LedgerEntryRecorded::dispatch($entry);

            return $entry;
        });
    }

    /**
     * Compute a SHA-256 chain hash from the given components.
     */
    public function buildChainHash(
        ?string $parentHash,
        string $entryType,
        string $subjectType,
        string $subjectId,
        string $actorId,
        array $payload,
        string $signedAt,
    ): string {
        $data = implode('|', [
            $parentHash ?? '',
            $entryType,
            $subjectType,
            $subjectId,
            $actorId,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $signedAt,
        ]);

        return hash('sha256', $data);
    }

    /**
     * Attach an uploaded file as evidence to a ledger entry.
     * Computes a SHA-256 checksum of the file contents at upload time.
     */
    public function attachEvidence(TrustLedgerEntry $entry, UploadedFile $file): TrustEvidenceAttachment
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $path     = $file->store('trust-evidence/' . $entry->company_id, 'local');

        return TrustEvidenceAttachment::create([
            'ledger_entry_id' => $entry->id,
            'file_path'       => $path,
            'file_name'       => $file->getClientOriginalName(),
            'mime_type'       => $file->getClientMimeType(),
            'file_size'       => $file->getSize(),
            'checksum'        => $checksum,
            'attachment_type' => 'photo',
            'captured_at'     => now(),
        ]);
    }

    /**
     * Return the full chain of ledger entries for a subject model.
     *
     * @return Collection<int, TrustLedgerEntry>
     */
    public function getChain(Model $subject): Collection
    {
        return TrustLedgerEntry::query()
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey())
            ->orderBy('id')
            ->get();
    }

    /**
     * Verify that every entry in the chain for a given subject is intact.
     */
    public function verifyChain(Model $subject): bool
    {
        $entries = $this->getChain($subject);

        foreach ($entries as $entry) {
            if (! $this->verifyEntry($entry)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Re-compute a single entry's hash and compare to the stored value.
     */
    public function verifyEntry(TrustLedgerEntry $entry): bool
    {
        $expected = $this->buildChainHash(
            $entry->parent_hash,
            $entry->entry_type,
            $entry->subject_type,
            (string) $entry->subject_id,
            (string) ($entry->actor_id ?? ''),
            $entry->payload,
            $entry->signed_at->toIso8601String(),
        );

        return hash_equals($expected, $entry->chain_hash);
    }

    /**
     * Seal the chain for a company at the current point in time.
     *
     * The seal_hash covers all chain hashes concatenated, giving a
     * single fingerprint of the entire chain state.
     */
    public function sealChain(int $companyId, ?User $actor = null): TrustChainSeal
    {
        return DB::transaction(function () use ($companyId, $actor) {
            $entries = TrustLedgerEntry::query()
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            $rootHash = hash('sha256', $entries->pluck('chain_hash')->implode('|'));
            $sealHash = hash('sha256', $rootHash . '|' . now()->toIso8601String() . '|' . $companyId);

            $seal = TrustChainSeal::create([
                'company_id'  => $companyId,
                'sealed_at'   => now(),
                'entry_count' => $entries->count(),
                'root_hash'   => $rootHash,
                'seal_hash'   => $sealHash,
                'sealed_by'   => $actor?->id,
            ]);

            ChainSealed::dispatch($seal);

            return $seal;
        });
    }
}
