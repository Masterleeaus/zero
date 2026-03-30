<?php
namespace Modules\ManagedPremises\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyApproval extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'pm_property_approvals';

    protected $fillable = [
        'company_id','property_id','subject','requested_by','requested_to','status',
        'request_payload','decision_payload','requested_at','decided_at'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'decision_payload' => 'array',
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function property(){ return $this->belongsTo(Property::class, 'property_id'); }
}
