<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;
use Illuminate\Support\Str;

class DocumentShareLink extends Model
{
    use BelongsToTenant;

    protected $table = 'document_share_links';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'token',
        'expires_at',
        'created_by',
        'note',
        'revoked_at',
        'revoked_by',
        'max_views',
        'views_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
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
