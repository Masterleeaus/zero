<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    /**
     * Field-service tracking modes (mirrors Odoo product.field_service_tracking).
     * - no   : no FSM job generated
     * - sale : one job per quote (when quote is accepted)
     * - line : one job per quote line (when quote is accepted)
     */
    public const TRACKING_NONE = 'no';
    public const TRACKING_SALE = 'sale';
    public const TRACKING_LINE = 'line';

    public const TRACKING_OPTIONS = [
        self::TRACKING_NONE,
        self::TRACKING_SALE,
        self::TRACKING_LINE,
    ];

    protected $fillable = [
        'company_id',
        'created_by',
        'quote_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'line_total',
        'sort_order',
        'field_service_tracking',
        'service_tracking_type',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected $attributes = [
        'field_service_tracking' => self::TRACKING_NONE,
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Service jobs generated from this quote line.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Work\ServiceJob>
     */
    public function serviceJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Work\ServiceJob::class, 'sale_line_id');
    }

    /**
     * Whether this line should generate a field-service job on quote acceptance.
     */
    public function generatesFieldWork(): bool
    {
        return $this->field_service_tracking !== self::TRACKING_NONE;
    }
}
