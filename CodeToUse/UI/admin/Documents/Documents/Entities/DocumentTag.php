<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentTag extends Model
{
    use BelongsToTenant;

    protected $table = 'documents_tags';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'bg_color',
        'text_color',
    ];

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'documents_document_tag', 'tag_id', 'document_id')
            ->withTimestamps()
            ->wherePivot('tenant_id', documents_tenant_id());
    }
}
