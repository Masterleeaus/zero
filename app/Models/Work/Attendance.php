<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Models\Work\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(static function (Attendance $attendance) {
            if ($attendance->check_in_at && ! $attendance->status) {
                $attendance->status = 'checked_in';
            }

            if ($attendance->check_out_at && $attendance->check_in_at) {
                $attendance->duration_minutes = $attendance->check_out_at->diffInMinutes($attendance->check_in_at);
                $attendance->status = 'checked_out';
            }
        });

        static::creating(static function (Attendance $attendance) {
            if (! $attendance->status) {
                $attendance->status = 'planned';
            }

            if (! $attendance->company_id && $attendance->user) {
                $attendance->company_id = $attendance->user->company_id;
            }
        });
    }

    public function markLateIfNeeded(): void
    {
        if ($this->shift && $this->check_in_at && $this->shift->start_at && $this->check_in_at->gt($this->shift->start_at)) {
            $this->status = 'late';
            $this->save();
        }
    }

    public function shiftMismatch(): bool
    {
        return $this->shift && $this->check_in_at && $this->shift->start_at && $this->check_in_at->lt($this->shift->start_at->subMinutes(30));
    }

    public static function statusSummary(int $companyId): array
    {
        return static::query()
            ->where('company_id', $companyId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public static function markMissedForShift(Shift $shift): void
    {
        $hasAttendance = static::query()
            ->where('company_id', $shift->company_id)
            ->where('shift_id', $shift->id)
            ->exists();

        if (! $hasAttendance && $shift->end_at && $shift->end_at->isPast()) {
            static::create([
                'company_id' => $shift->company_id,
                'user_id'    => $shift->user_id,
                'service_job_id' => $shift->service_job_id,
                'shift_id'   => $shift->id,
                'status'     => 'missed',
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
