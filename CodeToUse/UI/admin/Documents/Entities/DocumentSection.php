<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class DocumentSection extends Model
{
    use CompanyScoped;
    protected $table = 'document_sections';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'key',
        'label',
        'content',
        'sort_order',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}