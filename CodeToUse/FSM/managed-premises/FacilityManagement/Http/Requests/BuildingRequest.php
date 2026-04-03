<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class BuildingRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['site_id'=>'nullable|integer','name'=>'required|string|max:150','code'=>'nullable|string|max:50','address'=>'nullable|string']; }
}