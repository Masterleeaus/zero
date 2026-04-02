<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Photo, signature, or document attached to an InspectionInstance.
 *
 * Attachment types: photo | signature | document | video
 */
class InspectionAttachment extends Model
{
    use BelongsToCompany;

    protected $table = 'inspection_attachments';

    protected $fillable = [
        'company_id',
        'inspection_instance_id',
        'inspection_response_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'attachment_type',
        'caption',
        'uploaded_by',
    ];

    protected $attributes = [
        'attachment_type' => 'photo',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function instance(): BelongsTo
    {
        return $this->belongsTo(InspectionInstance::class, 'inspection_instance_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(InspectionResponse::class, 'inspection_response_id');
    }
}
