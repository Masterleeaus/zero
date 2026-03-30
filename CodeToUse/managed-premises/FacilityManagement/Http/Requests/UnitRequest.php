<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UnitRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['building_id'=>'nullable|integer','unit_type_id'=>'nullable|integer','code'=>'required|string|max:100','name'=>'nullable|string|max:150','floor'=>'nullable|string|max:50','status'=>'nullable|string|max:50']; }
}