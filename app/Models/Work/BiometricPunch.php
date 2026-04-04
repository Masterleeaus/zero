<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricPunch extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'punch_type',
        'punch_source',
        'punched_at',
        'latitude',
        'longitude',
        'device_id',
        'raw_payload',
        'attendance_id',
    ];

    protected $casts = [
        'punched_at'  => 'datetime',
        'raw_payload' => 'array',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
