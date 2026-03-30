<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class UnitType extends Model {
  use AppliesTenantScope;
  protected $table = 'unit_types';
  protected $fillable = ['name','code','description','meta'];
  protected $casts = ['meta'=>'array'];
  public function units(){ return $this->hasMany(Unit::class, 'unit_type_id'); }
}