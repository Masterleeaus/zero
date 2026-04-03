<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentVersion extends Model
{
    use BelongsToTenant;

    protected $table = 'document_versions';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'version_no',
        'reason',
        'version_hash',
        'snapshot',
        'created_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
