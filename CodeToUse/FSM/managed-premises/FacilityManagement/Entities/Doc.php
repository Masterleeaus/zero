<?php
namespace Modules\FacilityManagement\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\FacilityManagement\Support\AppliesTenantScope;

class Doc extends Model {
  use AppliesTenantScope;
  protected $table = 'facility_docs';
  protected $fillable = ['scope_type','scope_id','doc_type','path','issued_at','expires_at','status'];
  protected $casts = ['issued_at'=>'date','expires_at'=>'date'];
}