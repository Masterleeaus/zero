<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Occupancy extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_occupancies';
  protected $fillable = ['unit_id','tenant_type','tenant_id','start_date','end_date','status','contract_ref'];
  protected $casts = ['start_date'=>'date','end_date'=>'date'];
  public function unit(){ return $this->belongsTo(Unit::class); }
}