<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AssetRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['asset_type'=>'required|string|max:100','label'=>'required|string|max:150','serial_no'=>'nullable|string|max:150','status'=>'nullable|string|max:50','installed_at'=>'nullable|date','next_service_at'=>'nullable|date']; }
}