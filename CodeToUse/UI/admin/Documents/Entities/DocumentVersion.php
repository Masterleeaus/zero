<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class DocumentVersion extends Model
{
    use CompanyScoped;
    protected $table = 'document_versions';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'version_no',
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