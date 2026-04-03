<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class OccupancyRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['unit_id'=>'required|integer','tenant_type'=>'nullable|string','tenant_id'=>'nullable|integer','start_date'=>'nullable|date','end_date'=>'nullable|date','status'=>'nullable|string','contract_ref'=>'nullable|string']; }
}