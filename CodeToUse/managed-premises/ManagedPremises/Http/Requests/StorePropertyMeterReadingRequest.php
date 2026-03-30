<?php

namespace Modules\ManagedPremises\Http\Requests;

use App\Http\Requests\CoreRequest;

class StorePropertyMeterReadingRequest extends CoreRequest
{
    public function authorize(): bool
    {
        return user()->permission('managedpremises.meters.manage') !== 'none';
    }

    public function rules(): array
    {
        return [
            'meter_type' => 'required|string|max:50',
            'reading_date' => 'required|date',
            'current_reading' => 'required|numeric|min:0',
            'previous_reading' => 'nullable|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'unit_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ];
    }
}
