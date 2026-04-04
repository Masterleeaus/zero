<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic facility document.
 *
 * Can be attached to Premises, Building, or Unit.
 *
 * Document types: site_document | floorplan | compliance_doc | permit | safety_doc | other
 * Status values:  valid | expired | superseded | archived
 *
 * MODULE 08 additions:
 *   document_category: procedure|safety|compliance|regulatory|handover|sop|msds|permit
 *   applies_to_asset_types / applies_to_job_types / applies_to_service_types: json arrays
 *   access_level_minimum, requires_certification, is_mandatory, version
 *   supersedes_id (self-referential), review_due_at, embedding_vector
 */
class FacilityDocument extends Model
{
    use HasFactory;
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
        // MODULE 08 — injection metadata
        'document_category',
        'applies_to_asset_types',
        'applies_to_job_types',
        'applies_to_service_types',
        'access_level_minimum',
        'requires_certification',
        'is_mandatory',
        'version',
        'supersedes_id',
        'review_due_at',
        'embedding_vector',
    ];

    protected $casts = [
        'issued_at'               => 'date',
        'expires_at'              => 'date',
        'review_due_at'           => 'date',
        'applies_to_asset_types'  => 'array',
        'applies_to_job_types'    => 'array',
        'applies_to_service_types' => 'array',
        'embedding_vector'        => 'array',
        'is_mandatory'            => 'boolean',
        'access_level_minimum'    => 'integer',
    ];

    protected $attributes = [
        'doc_type'     => 'site_document',
        'status'       => 'valid',
        'is_mandatory' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Jobs that have had this document injected. */
    public function injectedJobs(): HasMany
    {
        return $this->hasMany(JobInjectedDocument::class, 'document_id');
    }

    /** Injection rules that reference this document. */
    public function injectionRules(): HasMany
    {
        return $this->hasMany(DocumentInjectionRule::class, 'document_id');
    }

    /** The document that supersedes this one (i.e., newer version). */
    public function supersededBy(): HasMany
    {
        return $this->hasMany(self::class, 'supersedes_id');
    }

    /** The document that this one supersedes (i.e., older version). */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isReviewDue(): bool
    {
        return $this->review_due_at !== null && $this->review_due_at->isPast();
    }

    public function isSuperseded(): bool
    {
        return $this->supersededBy()->exists();
    }
}
