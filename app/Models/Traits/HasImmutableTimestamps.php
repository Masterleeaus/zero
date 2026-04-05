<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * HasImmutableTimestamps — enforces append-only / write-once semantics on a model.
 *
 * Intended for OmniMessage and OmniCallLog where rows must never be updated
 * after they have been created.
 *
 * Usage:
 *   1. Add the trait to the model.
 *   2. Disable Laravel timestamps: public $timestamps = false;
 *   3. Declare a $immutableColumns array if specific columns must be write-once.
 */
trait HasImmutableTimestamps
{
    protected static function bootHasImmutableTimestamps(): void
    {
        // Block any UPDATE query on an existing record.
        static::updating(static function (self $model): bool {
            // Allow updates ONLY to columns not in the write-once list.
            $guarded = $model->getImmutableColumns();

            foreach ($guarded as $column) {
                if ($model->isDirty($column) && ! empty($model->getOriginal($column))) {
                    // Silently revert the dirty column rather than throwing to avoid
                    // breaking callers that do partial saves elsewhere.
                    $model->syncOriginalAttribute($column);
                }
            }

            return true;
        });
    }

    /**
     * Columns that may be set once but never overwritten.
     *
     * @return list<string>
     */
    public function getImmutableColumns(): array
    {
        return property_exists($this, 'immutableColumns') ? $this->immutableColumns : [];
    }

    /**
     * Scope: records created within the given number of days.
     */
    public function scopeRecentDays(Builder $query, int $days): Builder
    {
        return $query->where($this->getCreatedAtColumn(), '>=', now()->subDays($days));
    }
}
