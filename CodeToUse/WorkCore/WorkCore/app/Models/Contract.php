<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Service Agreement
 *
 * @property int $id
 * @property int $client_id
 * @property string $subject
 * @property string $amount
 * @property string $original_amount
 * @property int|null $contract_type_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property string $original_start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $original_end_date
 * @property string|null $description
 * @property string|null $contract_name
 * @property string|null $company_logo
 * @property string|null $alternate_address
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $contract_detail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User $customer
 * @property-read \App\Models\ContractType|null $contractType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractDiscussion[] $discussion
 * @property-read int|null $discussion_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContractRenew[] $renewHistory
 * @property-read int|null $renew_history_count
 * @property-read \App\Models\ContractSign|null $signature
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereAlternateAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereContractDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereContractName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereContractTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereOriginalEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereOriginalStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereUpdatedAt($value)
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereHash($value)
 * @property int|null $currency_id
 * @property-read \App\Models\Currency|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCurrencyId($value)
 * @property string|null $event_id
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereEventId($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCompanyId($value)
 * @property int|null $contract_number
 * @property int|null $project_id
 * @property string|null $contract_note
 * @property-read mixed $extras
 * @property-read \App\Models\Site|null $site
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereContractNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereProjectId($value)
 * @property string|null $company_sign
 * @property string|null $sign_date
 * @property-read mixed $company_signature
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereCompanySign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereSignDate($value)
 * @property string|null $original_contract_number
 * @method static \Illuminate\Database\Eloquent\Builder|Service Agreement whereOriginalContractNumber($value)
 * @mixin \Eloquent
 */
class Service Agreement extends BaseModel
{

    use CustomFieldsTrait, HasFactory, HasCompany;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sign_date' => 'datetime',
    ];

    protected $with = [];

    protected $appends = ['image_url', 'company_signature'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Service Agreement';

    public function signer()
    {
        return $this->belongsTo(User::class, 'sign_by');
    }

    public function getImageUrlAttribute()
    {
        return ($this->company_logo) ? asset_url_local_s3('service agreement-logo/' . $this->company_logo) : $this->company->logo_url;
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'project_id')->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function signature(): HasOne
    {
        return $this->hasOne(ContractSign::class, 'contract_id');
    }

    public function discussion(): HasMany
    {
        return $this->hasMany(ContractDiscussion::class)->orderByDesc('id');
    }

    public function renewHistory(): HasMany
    {
        return $this->hasMany(ContractRenew::class, 'contract_id')->orderByDesc('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractFile::class, 'contract_id')->orderByDesc('id');
    }

    public static function lastContractNumber()
    {
        return (int)Service Agreement::orderBy('id', 'desc')->first()?->original_contract_number ?? 0;
    }

    public function formatContractNumber()
    {
        $invoiceSettings = company() ? company()->invoiceSetting : $this->company->invoiceSetting;
        return \App\Helper\NumberFormat::service agreement($this->contract_number, $invoiceSettings);
    }

    public function getCompanySignatureAttribute()
    {
        return asset_url_local_s3('service agreement/sign/' . $this->company_sign);
    }

}
