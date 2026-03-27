<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folders extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'name',
        'created_by',
        'company_id',
        'team_id',
    ];

    protected static function booted(): void
    {
        static::creating(static function (Folders $folder) {
            if ($folder->company_id === null && auth()->check()) {
                $folder->company_id = tenant();
            }
        });
    }

    public function userOpenais(): HasMany
    {
        return $this->hasMany(UserOpenai::class, 'folder_id');
    }
}
