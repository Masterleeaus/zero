<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Unit extends Model {
  use AppliesTenantScope;
  protected $table = 'units';
  protected $fillable = ['building_id','unit_type_id','code','name','floor','meta','status'];
  protected $casts = ['meta'=>'array'];
  public function building(){ return $this->belongsTo(Building::class); }
  public function type(){ return $this->belongsTo(UnitType::class, 'unit_type_id'); }
  public function meters(){ return $this->hasMany(Meter::class); }
  public function occupancies(){ return $this->hasMany(Occupancy::class); }
}