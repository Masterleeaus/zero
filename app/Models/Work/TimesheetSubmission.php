<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimesheetSubmission extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'week_start',
        'week_end',
        'total_hours',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $attributes = [
        'status'      => 'draft',
        'total_hours' => 0,
    ];

    protected $casts = [
        'week_start'   => 'date',
        'week_end'     => 'date',
        'total_hours'  => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function timelogs(): HasMany
    {
        return $this->hasMany(Timelog::class, 'user_id', 'user_id')
            ->whereBetween('started_at', [$this->week_start, $this->week_end]);
    }
}
