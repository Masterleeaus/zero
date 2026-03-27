<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Auth\GenericUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait OwnedByUser
{
    protected static function bootOwnedByUser(): void
    {
        static::creating(static function ($model) {
            if (is_null($model->created_by) && ($user = Auth::user())) {
                $model->created_by = $user->getAuthIdentifier();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCreatedBy(Builder $query, int|GenericUser $user): Builder
    {
        $userId = $user instanceof GenericUser ? $user->getAuthIdentifier() : $user;

        return $query->where($query->qualifyColumn('created_by'), $userId);
    }
}
