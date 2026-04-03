<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class DocumentMetadata extends Model
{
    use CompanyScoped;
    protected $table = 'document_metadata';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'meta_key',
        'meta_value',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}