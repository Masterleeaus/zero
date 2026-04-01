<?php

declare(strict_types=1);

namespace App\Models\Crm;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Money\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'name',
        'email',
        'phone',
        'status',
        'source',
        'notes',
        'team_id',
        'quote_id',
        'follow_up_at',
        'follow_up_note',
        'follow_up_done',
    ];

    protected $attributes = [
        'status'         => 'open',
        'follow_up_done' => false,
    ];

    protected $casts = [
        'follow_up_at'   => 'datetime',
        'follow_up_done' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function scopeDueFollowUps(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId)
            ->where('follow_up_at', '<=', now())
            ->where('follow_up_done', false)
            ->whereNotNull('follow_up_at');
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
