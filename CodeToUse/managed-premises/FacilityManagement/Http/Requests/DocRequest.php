<?php
namespace Modules\FacilityManagement\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class DocRequest extends FormRequest {
  public function authorize(): bool { return $this->user()?->can('facilities.manage') ?? false; }
  public function rules(): array { return ['scope_type'=>'required|string','scope_id'=>'required|integer','doc_type'=>'required|string','issued_at'=>'nullable|date','expires_at'=>'nullable|date','status'=>'nullable|string']; }
}