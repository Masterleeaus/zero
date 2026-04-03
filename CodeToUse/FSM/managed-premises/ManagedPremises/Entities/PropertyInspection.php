<?php
namespace Modules\ManagedPremises\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyInspection extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'pm_property_inspections';

    protected $fillable = [
        'company_id','property_id','inspection_type','scheduled_for','inspected_by',
        'status','score','findings','actions','completed_at'
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
        'findings' => 'array',
        'actions' => 'array',
    ];

    public function property(){ return $this->belongsTo(Property::class, 'property_id'); }
}
