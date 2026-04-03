<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class MeterRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['unit_id'=>'nullable|integer','asset_id'=>'nullable|integer','meter_type'=>'required|string','barcode'=>'nullable|string']; }
}