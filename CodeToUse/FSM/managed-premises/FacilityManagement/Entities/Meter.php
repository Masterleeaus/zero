<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Meter extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_meters';
  protected $fillable = ['unit_id','asset_id','meter_type','barcode','last_reading','last_read_at'];
  protected $casts = ['last_read_at'=>'datetime'];
  public function reads(){ return $this->hasMany(MeterRead::class); }
}