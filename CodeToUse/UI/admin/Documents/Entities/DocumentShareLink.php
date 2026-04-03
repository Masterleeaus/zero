<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Documents\Traits\CompanyScoped;

class DocumentShareLink extends Model
{
    use CompanyScoped;
    protected $table = 'document_share_links';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'token',
        'expires_at',
        'created_by',
        'note',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->token)) {
                $model->token = Str::random(48);
            }
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}