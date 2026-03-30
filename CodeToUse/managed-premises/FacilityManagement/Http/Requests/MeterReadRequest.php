<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class MeterReadRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['meter_id'=>'required|integer','reading'=>'required|numeric','read_at'=>'nullable|date','source'=>'nullable|string']; }
}