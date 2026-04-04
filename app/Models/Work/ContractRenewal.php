<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRenewal extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'contract_renewals';

    protected $guarded = [];

    protected $casts = [
        'renewed_at'      => 'datetime',
        'previous_expiry' => 'date',
        'new_expiry'      => 'date',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function renewedTo(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'renewed_to_id');
    }

    public function renewedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'renewed_by');
    }
}
