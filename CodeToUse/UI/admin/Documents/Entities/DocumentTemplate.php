<?php

namespace Modules\Documents\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class DocumentTemplate extends Model
{
    use CompanyScoped;
    protected $table = 'documents_templates';

    protected $fillable = [
        'tenant_id','name','slug','category','subcategory','description',
        'body_html','body_markdown','placeholders','is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'subcategory' => 'string',
        'is_active' => 'boolean',
    ];
}