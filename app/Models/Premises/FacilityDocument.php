<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic facility document.
 *
 * Can be attached to Premises, Building, or Unit.
 *
 * Document types: site_document | floorplan | compliance_doc | permit | safety_doc | other
 * Status values:  valid | expired | superseded | archived
 */
class FacilityDocument extends Model
{
    use BelongsToCompany;

    protected $table = 'facility_documents';

    protected $fillable = [
        'company_id',
        'documentable_type',
        'documentable_id',
        'doc_type',
        'title',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'issued_at',
        'expires_at',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issued_at'  => 'date',
        'expires_at' => 'date',
    ];

    protected $attributes = [
        'doc_type' => 'site_document',
        'status'   => 'valid',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
