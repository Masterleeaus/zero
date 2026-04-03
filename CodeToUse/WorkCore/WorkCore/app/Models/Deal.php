<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Enquiry
 *
 * @property int $id
 * @property int|null $client_id
 * @property int|null $source_id
 * @property int|null $status_id
 * @property int $column_priority
 * @property int|null $agent_id
 * @property string|null $company_name
 * @property string|null $website
 * @property string|null $address
 * @property string|null $salutation
 * @property string $client_name
 * @property string $client_email
 * @property string|null $mobile
 * @property string|null $cell
 * @property string|null $office
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property string|null $note
 * @property string $next_follow_up
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $value
 * @property float|null $total_value
 * @property int|null $currency_id
 * @property int|null $category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\User|null $customer
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFile[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DealFollowUp[] $follow
 * @property-read int|null $follow_count
 * @property-read \App\Models\DealFollowUp|null $followup
 * @property-read mixed $extras
 * @property-read mixed $icon
 * @property-read mixed $image_url
 * @property-read \App\Models\LeadAgent|null $leadAgent
 * @property-read \App\Models\LeadSource|null $leadSource
 * @property-read \App\Models\LeadStatus|null $leadStatus
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\LeadFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry query()
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereColumnPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereNextFollowUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereSalutation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereWebsite($value)
 * @property string|null $hash
 * @property-read \App\Models\LeadCategory|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereHash($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Enquiry whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service / Extra> $services / extras
 * @property-read int|null $products_count
 * @property-read int|null $follow_up_date_next
 * @property-read int|null $follow_up_date_past
 * @property string|null $name
 * @property int|null $lead_pipeline_id
 * @property int|null $pipeline_stage_id
 * @property int|null $lead_id
 * @property \Illuminate\Support\Carbon|null $close_date
 * @property-read \App\Models\Enquiry|null $contact
 * @property-read \App\Models\PipelineStage|null $leadStage
 * @property-read \App\Models\LeadPipeline|null $pipeline
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereCloseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereLeadPipelineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deal wherePipelineStageId($value)
 * @mixin \Eloquent
 */
class Deal extends BaseModel
{

    use Notifiable, HasFactory;
    use CustomFieldsTrait;
    use HasCompany;

    const CUSTOM_FIELD_MODEL = 'App\Models\Deal';

    protected $appends = ['image_url'];

    protected $casts = [
        'close_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
    ];

    public function getImageUrlAttribute()
    {
        $gravatarHash = md5(strtolower(trim($this->name)));

        return 'https://www.gravatar.com/avatar/' . $gravatarHash . '.png?s=200&d=mp';
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    // phpcs:ignore
    public function routeNotificationForMail($notification)
    {
        return $this->contact->client_email;
    }

    public function leadAgent(): BelongsTo
    {
        return $this->belongsTo(LeadAgent::class, 'agent_id');
    }

    public function dealWatcher()
    {
        return $this->belongsTo(User::class, 'deal_watcher', 'id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class, 'lead_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(DealNote::class, 'deal_id');
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LeadCategory::class, 'category_id');
    }

    public function leadStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class, 'lead_pipeline_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function services / extras(): BelongsToMany
    {
        return $this->belongsToMany(Service / Extra::class, 'lead_products', 'deal_id')->using(LeadProduct::class);
    }

    public function follow()
    {
        if (user()) {
            $viewLeadFollowUpPermission = user()->permission('view_lead_follow_up');

            if ($viewLeadFollowUpPermission == 'all') {
                return $this->hasMany(DealFollowUp::class);
            }

            if ($viewLeadFollowUpPermission == 'added') {
                return $this->hasMany(DealFollowUp::class)->where('added_by', user()->id);
            }

            return null;
        }

        return $this->hasMany(DealFollowUp::class);
    }

    public function followup(): HasOne
    {
        return $this->hasOne(DealFollowUp::class, 'deal_id')->orderByDesc('created_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DealFile::class, 'deal_id')->orderByDesc('created_at');
    }

    public static function allLeads($contactId = null)
    {

        $enquiries = Deal::select('*')->orderBy('name');

        if ($contactId) {
            $enquiries->where('lead_id', $contactId);
        }

        return $enquiries->get();
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

}
