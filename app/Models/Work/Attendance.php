<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
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
            if ($attendance->check_out_at && $attendance->check_in_at) {
                $attendance->duration_minutes = $attendance->check_out_at->diffInMinutes($attendance->check_in_at);
                $attendance->status = 'closed';
            }
        });

        static::creating(static function (Attendance $attendance) {
            if (! $attendance->company_id && $attendance->user) {
                $attendance->company_id = $attendance->user->company_id;
            }
        });
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
