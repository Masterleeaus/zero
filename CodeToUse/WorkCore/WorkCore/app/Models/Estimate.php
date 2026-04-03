<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Quote
 *
 * @property int $id
 * @property int $client_id
 * @property string|null $estimate_number
 * @property \Illuminate\Support\Carbon $valid_till
 * @property float $sub_total
 * @property float $discount
 * @property string $discount_type
 * @property float $total
 * @property int|null $currency_id
 * @property string $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $send_status
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $customer
 * @property-read \App\Models\Currency|null $currency
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read mixed $valid_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EstimateItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\AcceptEstimate|null $sign
 * @method static \Illuminate\Database\Eloquent\Builder|Quote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Quote query()
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereEstimateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereSendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereValidTill($value)
 * @property string|null $hash
 * @property int|null $unit_id
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereHash($value)
 * @property string $calculate_tax
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereCalculateTax($value)
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereDescription($value)
 * @property int|null $company_id
 * @property-read \App\Models\ClientDetails $clientdetails
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereCompanyId($value)
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property string|null $ip_address
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereUnitId($value)
 * @property string|null $original_estimate_number
 * @method static \Illuminate\Database\Eloquent\Builder|Quote whereOriginalEstimateNumber($value)
 * @mixin \Eloquent
 */
class Quote extends BaseModel
{

    use Notifiable, CustomFieldsTrait, HasCompany;

    protected $casts = [
        'valid_till' => 'datetime',
        'last_viewed' => 'datetime',
    ];
    protected $appends = ['total_amount', 'valid_date'];
    protected $with = ['currency'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Quote';

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'project_id')->withTrashed();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function clientdetails(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class, 'client_id', 'user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function sign(): HasOne
    {
        return $this->hasOne(AcceptEstimate::class, 'estimate_id');
    }

    public function getTotalAmountAttribute()
    {
        return (!is_null($this->total) && isset($this->currency) && !is_null($this->currency->currency_symbol)) ? $this->currency->currency_symbol . $this->total : '';
    }

    public function getValidDateAttribute()
    {
        return !is_null($this->valid_till) ? Carbon::parse($this->valid_till)->format('d F, Y') : '';
    }

    public function formatEstimateNumber()
    {
        $invoiceSettings = (company()) ? company()->invoiceSetting : $this->company->invoiceSetting;
        return \App\Helper\NumberFormat::quote($this->estimate_number, $invoiceSettings);
    }

    public static function lastEstimateNumber()
    {
        return (int)Quote::orderBy('id', 'desc')->first()?->original_estimate_number ?? 0;
    }

    public function estimateRequest(): BelongsTo
    {
        return $this->belongsTo(EstimateRequest::class, 'estimate_request_id');
    }

}
