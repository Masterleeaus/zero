<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Traits\CompanyScoped;

class Document extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'category',
        'subcategory',
        'template_slug',
        'body_markdown',
        'body_html',
        'status',
        'qr_slug',
        'effective_at',
        'review_at',
        'trade',
        'role',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
        'review_at' => 'datetime',
    ];

    public function scopeSwms($query)
    {
        return $query->where('type', 'swms');
    }

    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class, 'document_id');
    }

    public function sections()
    {
        return $this->hasMany(DocumentSection::class, 'document_id')->orderBy('sort_order');
    }

    public function metadata()
    {
        return $this->hasMany(DocumentMetadata::class, 'document_id');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class, 'document_id')->orderByDesc('version_no');
    }

    public function shareLinks()
    {
        return $this->hasMany(DocumentShareLink::class, 'document_id');
    }

    public function links()
    {
        return $this->hasMany(DocumentLink::class, 'document_id');
    }
}