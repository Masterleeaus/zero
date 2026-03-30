<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'site_id',
        'quote_number',
        'title',
        'status',
        'issue_date',
        'valid_until',
        'currency',
        'subtotal',
        'tax',
        'total',
        'notes',
        'checklist_template',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'total'      => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'checklist_template' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }
}
