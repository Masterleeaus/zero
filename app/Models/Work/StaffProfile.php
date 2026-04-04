<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Work\Department;
use App\Models\Work\EmploymentLifecycleState;

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
        'department_id',
        'employment_type',
        'employment_status',
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
        'status'            => 'active',
        'employment_status' => 'active',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function lifecycleStates(): HasMany
    {
        return $this->hasMany(EmploymentLifecycleState::class, 'staff_profile_id');
    }

}
