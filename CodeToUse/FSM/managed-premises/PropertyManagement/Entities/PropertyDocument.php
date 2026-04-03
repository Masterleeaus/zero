<?php
namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PropertyManagement\Entities\Concerns\BelongsToCompany;

class PropertyDocument extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'pm_property_documents';

    protected $fillable = [
        'company_id','property_id','name','doc_type','stored_path','mime','size_bytes',
        'uploaded_by','notes'
    ];

    public function property(){ return $this->belongsTo(Property::class, 'property_id'); }
}
