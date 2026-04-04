<?php

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'user_id',
        'vehicle_id',
        'service_job_id',
        'start_at',
        'end_at',
        'status',
        'shift_type',
        'recurring_days',
        'location_id',
        'is_published',
    ];

    protected $casts = [
        'start_at'       => 'datetime',
        'end_at'         => 'datetime',
        'recurring_days' => 'array',
        'is_published'   => 'boolean',
    ];

    public function scopeUnassigned($query)
    {
        return $query->whereNull('service_job_id');
    }

    public function scopeOverdue($query)
    {
        return $query->where('end_at', '<', now())->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function assignServiceJob(ServiceJob $job): void
    {
        $this->service_job_id = $job->id;
        $this->status         = 'assigned';
        $this->save();
    }

    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
