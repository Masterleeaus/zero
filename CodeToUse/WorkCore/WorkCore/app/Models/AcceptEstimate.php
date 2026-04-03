<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\AcceptEstimate
 *
 * @property int $id
 * @property int $estimate_id
 * @property string $full_name
 * @property string $email
 * @property string $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Quote|null $quote
 * @method static \Illuminate\Database\Eloquent\Builder|AcceptEstimate whereCompanyId($value)
 * @mixin \Eloquent
 */
class AcceptEstimate extends BaseModel
{

    use HasCompany;

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function getSignatureAttribute()
    {
        return asset_url_local_s3('quote/accept/' . $this->attributes['signature']);
    }

}
