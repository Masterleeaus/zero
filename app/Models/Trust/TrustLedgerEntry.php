<?php

declare(strict_types=1);

namespace App\Models\Trust;

use App\Exceptions\Trust\ImmutableRecordException;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An immutable, cryptographically-chained ledger entry.
 *
 * Entry types: job_completed | inspection_passed | inspection_failed |
 *              checklist_completed | signature_captured | photo_attached |
 *              client_acknowledged | asset_serviced | override_applied
 *
 * Actor types: user | system | ai
 */
class TrustLedgerEntry extends Model
{
    use BelongsToCompany;

    protected $table = 'trust_ledger_entries';

    // No updated_at — this table is immutable.
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'chain_hash',
        'parent_hash',
        'entry_type',
        'subject_type',
        'subject_id',
        'actor_type',
        'actor_id',
        'payload',
        'signed_at',
        'created_at',
    ];

    protected $casts = [
        'payload'   => 'array',
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'actor_type' => 'user',
    ];

    // ── Valid entry types ────────────────────────────────────────────────────

    public const ENTRY_TYPES = [
        'job_completed',
        'inspection_passed',
        'inspection_failed',
        'checklist_completed',
        'signature_captured',
        'photo_attached',
        'client_acknowledged',
        'asset_serviced',
        'override_applied',
    ];

    public const ACTOR_TYPES = ['user', 'system', 'ai'];

    // ── Immutability guard ───────────────────────────────────────────────────

    /**
     * @throws ImmutableRecordException
     */
    public function save(array $options = []): bool
    {
        if ($this->exists && $this->isDirty()) {
            throw new ImmutableRecordException();
        }

        return parent::save($options);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function attachments(): HasMany
    {
        return $this->hasMany(TrustEvidenceAttachment::class, 'ledger_entry_id');
    }
}
