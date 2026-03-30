<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SiteRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['name'=>'required|string|max:150','code'=>'nullable|string|max:50','address'=>'nullable|string']; }
}