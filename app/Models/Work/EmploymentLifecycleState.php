<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentLifecycleState extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'employment_lifecycle_states';

    protected $fillable = [
        'company_id',
        'user_id',
        'staff_profile_id',
        'status',
        'previous_status',
        'notes',
        'changed_by',
        'effective_at',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
