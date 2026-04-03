<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class DocumentLink extends Model
{
    use CompanyScoped;
    protected $table = 'document_links';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'linked_type',
        'linked_id',
        'label',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}