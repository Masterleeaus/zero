<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'account_name',
        'bank_name',
        'account_number',
        'bsb',
        'currency',
        'is_default',
        'notes',
    ];

    protected $attributes = [
        'currency'   => 'AUD',
        'is_default' => false,
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
