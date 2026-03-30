<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Inspection extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_inspections';
  protected $fillable = ['scope_type','scope_id','checklist_json','inspector_id','status','scheduled_at','completed_at','result_json'];
  protected $casts = ['checklist_json'=>'array','result_json'=>'array','scheduled_at'=>'datetime','completed_at'=>'datetime'];
}