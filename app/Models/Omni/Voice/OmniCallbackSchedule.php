<?php

declare(strict_types=1);

namespace App\Models\Omni\Voice;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Omni\OmniCustomer;

/**
 * OmniCallbackSchedule — Pending customer callback request.
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $omni_customer_id
 * @property int|null    $assigned_to
 * @property string|null $phone_number
 * @property string|null $notes
 * @property string      $status
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $handled_at
 * @property array|null  $metadata
 */
class OmniCallbackSchedule extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_callback_schedules';

    protected $fillable = [
        'company_id',
        'omni_customer_id',
        'assigned_to',
        'phone_number',
        'notes',
        'status',
        'scheduled_at',
        'handled_at',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'handled_at'   => 'datetime',
        'metadata'     => 'array',
    ];

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }

    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending');
    }
}
