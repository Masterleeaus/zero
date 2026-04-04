<?php

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timelog extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(static function (Timelog $timelog) {
            if ($timelog->ended_at && $timelog->started_at) {
                $timelog->duration_minutes = $timelog->ended_at->diffInMinutes($timelog->started_at);
            }
        });

        static::creating(static function (Timelog $timelog) {
            if (! $timelog->company_id && $timelog->user) {
                $timelog->company_id = $timelog->user->company_id;
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
}
