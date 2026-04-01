<?php

declare(strict_types=1);

namespace App\Models\Crm;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'email',
        'phone',
        'status',
        'notes',
        'team_id',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'customer_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Scope: billable completed jobs with no invoice yet.
     */
    public function scopeHasUnbilledJobs(Builder $query): Builder
    {
        return $query->whereHas('serviceJobs', function (Builder $q) {
            $q->where('is_billable', true)
              ->whereNull('invoice_id')
              ->where('status', 'completed');
        });
    }

    /**
     * Query helper: returns the unbilled completed service jobs for this customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function unbilledJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->orderBy('date_end')
            ->get();
    }

    /**
     * Query helper: returns all pending (todo) activities across all jobs for this customer.
     *
     * Ordered by follow_up_at ASC (nulls last), then sequence ASC.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Work\JobActivity>
     */
    public function pendingActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Work\JobActivity::query()
            ->where('company_id', $this->company_id)
            ->where('state', 'todo')
            ->forCustomer($this->id)
            ->orderByRaw('follow_up_at IS NULL, follow_up_at ASC')
            ->orderBy('sequence')
            ->with(['job'])
            ->get();
    }
}
