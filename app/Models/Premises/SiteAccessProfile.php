<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Structured site access memory for a Premises.
 *
 * Captures key type/location, alarm codes, entry instructions, and
 * contact points.  Attached automatically to ServiceJob dispatch context.
 *
 * Key types: physical_key | lockbox | fob | keycard | combination
 */
class SiteAccessProfile extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'site_access_profiles';

    protected $fillable = [
        'company_id',
        'created_by',
        'premises_id',
        'key_type',
        'key_location',
        'key_reference',
        'entry_instructions',
        'lockbox_code',
        'alarm_code',
        'alarm_instructions',
        'parking_notes',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_role',
        'afterhours_contact_name',
        'afterhours_contact_phone',
        'additional_notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasAlarmCode(): bool
    {
        return ! empty($this->alarm_code);
    }

    public function hasAfterHoursContact(): bool
    {
        return ! empty($this->afterhours_contact_phone);
    }
}
