<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentFolder extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $table = 'document_folders';

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class, 'folder_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $folder) {
            if ($folder->position === null) {
                try {
                    $tenantId = documents_tenant_id();
                    if ($tenantId !== null) {
                        $max = self::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->max('position');
                        $folder->position = ($max ?? 0) + 1;
                    }
                } catch (\Throwable $e) {}
            }
        });
    }
}
