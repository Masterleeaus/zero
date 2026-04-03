<?php

namespace Modules\Documents\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentTemplate extends Model
{
    use BelongsToTenant;

    protected $table = 'documents_templates';

    protected $fillable = [
        'tenant_id','name','slug','category','subcategory','description',
        'body_html','body_markdown','placeholders','tags','trade','role_key','is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'tags' => 'array',
        'subcategory' => 'string',
        'is_active' => 'boolean',
    ];
}
