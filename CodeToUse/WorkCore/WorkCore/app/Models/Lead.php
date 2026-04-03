<?php

namespace App\Models;

use App\Enums\Salutation;
use App\Scopes\ActiveScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @mixin \Eloquent
 */
class Enquiry extends BaseModel
{

    use Notifiable, HasFactory;
    use CustomFieldsTrait;
    use HasCompany;

    const CUSTOM_FIELD_MODEL = 'App\Models\Enquiry';

    protected $appends = ['image_url', 'client_name_salutation'];

    protected $casts = [
        'salutation' => Salutation::class,
    ];

    public function getImageUrlAttribute()
    {
        $gravatarHash = !is_null($this->email) ? md5(strtolower(trim($this->email))) : '';

        return 'https://www.gravatar.com/avatar/' . $gravatarHash . '.png?s=200&d=mp';
    }

    public function clientNameSalutation(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ($this->salutation ? $this->salutation->label() . ' ' : '') . $this->client_name
        );
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
        return $this->email;
    }

    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LeadCategory::class, 'category_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(LeadNote::class, 'lead_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function leadOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_owner')->withoutGlobalScope(ActiveScope::class);
    }

    public static function allLeads($contactId = null)
    {
        // Retrieve user's enquiry view permission
        $viewLeadPermission = user()->permission('view_lead');

        // If the user has no permission to view enquiries
        if ($viewLeadPermission === 'none') {
            return collect();
        }

        // Initialize enquiry query
        $leadsQuery = Enquiry::select('*')->orderBy('client_name');

        if ($viewLeadPermission == 'owned') {
            $leadsQuery = $leadsQuery->where('lead_owner', user()->id);
        }

        if ($viewLeadPermission == 'added') {
            $leadsQuery = $leadsQuery->where('added_by', user()->id);
        }

        if ($viewLeadPermission == 'both') {
            $leadsQuery = $leadsQuery->where(function ($query) {
                $query->where('lead_owner', user()->id)
                      ->orWhere('added_by', user()->id);
            });
        }

        // Apply contact ID filter if provided
        if ($contactId) {
            $leadsQuery->where('id', $contactId);
        }

        // Retrieve enquiries
        return $leadsQuery->get();
    }

}
