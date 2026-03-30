<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Asset extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_assets';
  protected $fillable = ['site_id','building_id','unit_id','asset_type','label','serial_no','status','installed_at','next_service_at','meta'];
  protected $casts = ['installed_at'=>'date','next_service_at'=>'date','meta'=>'array'];
  public function unit(){ return $this->belongsTo(Unit::class); }
  public function building(){ return $this->belongsTo(Building::class); }
  public function site(){ return $this->belongsTo(Site::class); }
}