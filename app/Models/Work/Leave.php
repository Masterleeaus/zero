<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Models\Work\Shift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
class Leave extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LeaveHistory::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('start_date', '>=', now()->toDateString());
    }

    public static function conflictsWithShifts(int $companyId): int
    {
        return static::query()
            ->where('company_id', $companyId)
            ->whereExists(static function ($q) {
                $q->select(DB::raw(1))
                    ->from('shifts')
                    ->whereColumn('shifts.company_id', 'leaves.company_id')
                    ->whereColumn('shifts.user_id', 'leaves.user_id')
                    ->whereRaw('date(shifts.start_at) <= leaves.end_date')
                    ->whereRaw('date(shifts.end_at) >= leaves.start_date');
            })
            ->count();
    }

    public static function conflictsWithShift(Shift $shift): bool
    {
        return static::query()
            ->where('company_id', $shift->company_id)
            ->where('user_id', $shift->user_id)
            ->whereDate('start_date', '<=', optional($shift->end_at)?->toDateString())
            ->whereDate('end_date', '>=', optional($shift->start_at)?->toDateString())
            ->exists();
    }
}
