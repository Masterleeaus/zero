<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteTemplateItem extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'quote_template_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'sort_order',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuoteTemplate::class, 'quote_template_id');
    }
}
