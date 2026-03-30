<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Site extends Model {
  use AppliesTenantScope;
  protected $table = 'sites';
  protected $fillable = ['name','code','address','meta'];
  protected $casts = ['meta'=>'array'];
  public function buildings(){ return $this->hasMany(Building::class); }
}