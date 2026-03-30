<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Building extends Model {
  use AppliesTenantScope;
  protected $table = 'buildings';
  protected $fillable = ['site_id','name','code','address','meta'];
  protected $casts = ['meta'=>'array'];
  public function site(){ return $this->belongsTo(Site::class); }
  public function units(){ return $this->hasMany(Unit::class); }
}