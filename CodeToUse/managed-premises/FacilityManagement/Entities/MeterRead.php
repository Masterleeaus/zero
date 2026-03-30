<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class MeterRead extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_meter_reads';
  protected $fillable = ['meter_id','reading','read_at','reader_id','source'];
  protected $casts = ['read_at'=>'datetime'];
  public function meter(){ return $this->belongsTo(Meter::class); }
}