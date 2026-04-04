<?php

declare(strict_types=1);

namespace App\Models\Trust;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * File evidence attached to a ledger entry.
 *
 * The SHA-256 checksum is computed at upload time and stored here
 * to detect any subsequent tampering of the stored file.
 */
class TrustEvidenceAttachment extends Model
{
    protected $table = 'trust_evidence_attachments';

    protected $fillable = [
        'ledger_entry_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'checksum',
        'attachment_type',
        'captured_at',
    ];

    protected $casts = [
        'file_size'   => 'integer',
        'captured_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function ledgerEntry(): BelongsTo
    {
        return $this->belongsTo(TrustLedgerEntry::class, 'ledger_entry_id');
    }
}
