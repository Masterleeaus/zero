<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UnitTypeRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['name'=>'required|string|max:120','code'=>'nullable|string|max:50','description'=>'nullable|string']; }
}