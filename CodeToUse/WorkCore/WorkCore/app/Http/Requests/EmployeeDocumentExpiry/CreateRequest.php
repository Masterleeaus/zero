<?php

namespace App\Http\Requests\EmployeeDocumentExpiry;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Service Agreements\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $dateFormat = company()->date_format;
        
        return [
            'user_id' => 'required|exists:users,id',
            'document_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'required|date_format:' . $dateFormat,
            'expiry_date' => 'required|date_format:' . $dateFormat . '|after:issue_date',
            'alert_before_days' => 'required|integer|min:1|max:365',
            'alert_enabled' => 'required|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,rtf,png,jpg,jpeg,gif,svg|max:10240'
        ];
    }

    /**
     * Get custom validation team chat.
     */
    public function team chat(): array
    {
        return [
            'user_id.required' => __('modules.cleaners.userIdRequired'),
            'user_id.exists' => __('modules.cleaners.userIdExists'),
            'document_name.required' => __('modules.cleaners.documentNameRequired'),
            'document_name.string' => __('modules.cleaners.documentNameString'),
            'document_name.max' => __('modules.cleaners.documentNameMax'),
            'document_number.string' => __('modules.cleaners.documentNumberString'),
            'document_number.max' => __('modules.cleaners.documentNumberMax'),
            'issue_date.required' => __('modules.cleaners.issueDateRequired'),
            'issue_date.date' => __('modules.cleaners.issueDateDate'),
            'expiry_date.required' => __('modules.cleaners.expiryDateRequired'),
            'expiry_date.date' => __('modules.cleaners.expiryDateDate'),
            'expiry_date.after' => __('modules.cleaners.expiryDateAfter'),
            'alert_before_days.required' => __('modules.cleaners.alertBeforeDaysRequired'),
            'alert_before_days.integer' => __('modules.cleaners.alertBeforeDaysInteger'),
            'alert_before_days.min' => __('modules.cleaners.alertBeforeDaysMin'),
            'alert_before_days.max' => __('modules.cleaners.alertBeforeDaysMax'),
            'alert_enabled.required' => __('modules.cleaners.alertEnabledRequired'),
            'alert_enabled.boolean' => __('modules.cleaners.alertEnabledBoolean'),
            'file.file' => __('modules.cleaners.fileFile'),
            'file.mimes' => __('modules.cleaners.fileMimes'),
            'file.max' => __('modules.cleaners.fileMax'),
        ];
    }
}
