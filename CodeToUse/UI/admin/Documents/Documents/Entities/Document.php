<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class Document extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'category',
        'subcategory',
        'position',
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

    public function statusHistory()
    {
        return $this->hasMany(DocumentStatusHistory::class, 'document_id')->orderByDesc('id');
    }

    public function tags()
    {
        return $this->belongsToMany(DocumentTag::class, 'documents_document_tag', 'document_id', 'tag_id')
            ->withTimestamps()
            ->wherePivot('tenant_id', documents_tenant_id());
    }

    protected static function booted(): void
    {
        static::creating(function (self $doc) {
            if ($doc->position === null) {
                try {
                    $tenantId = documents_tenant_id();
                    if ($tenantId !== null) {
                        $max = self::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->max('position');
                        $doc->position = ($max ?? 0) + 1;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }

}
