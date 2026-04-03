<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'employee_number',
        'job_title',
        'department',
        'employment_type',
        'start_date',
        'end_date',
        'hourly_rate',
        'salary',
        'pay_frequency',
        'manager_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'hourly_rate' => 'decimal:2',
        'salary'      => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /** Staff profiles whose manager_id links to this profile's user_id. */
    public function managedStaff(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id', 'user_id');
    }

}
