@extends('panel.layout.app')
@section('title', __('Payroll Run') . ' ' . $payroll->reference)

@section('content')
    <div class="py-6 space-y-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-semibold">{{ __('Payroll Run') }}: {{ $payroll->reference }}</h1>
                <p class="text-gray-500 text-sm">{{ $payroll->period_start?->format('Y-m-d') }} – {{ $payroll->period_end?->format('Y-m-d') }}</p>
            </div>
            <div class="flex gap-2">
                @if($payroll->isDraft())
                    <form method="post" action="{{ route('dashboard.money.payroll.approve', $payroll) }}">
                        @csrf
                        <x-button type="submit" variant="primary">{{ __('Approve Payroll') }}</x-button>
                    </form>
                @endif
                <x-button variant="secondary" href="{{ route('dashboard.money.payroll.index') }}">{{ __('Back') }}</x-button>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded">
            <div><p class="text-xs text-gray-500">{{ __('Gross') }}</p><p class="font-medium">{{ number_format($payroll->total_gross, 2) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Tax') }}</p><p class="font-medium">{{ number_format($payroll->total_tax, 2) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Deductions') }}</p><p class="font-medium">{{ number_format($payroll->total_deductions, 2) }}</p></div>
            <div><p class="text-xs text-gray-500">{{ __('Net Pay') }}</p><p class="font-semibold text-green-700">{{ number_format($payroll->total_net, 2) }}</p></div>
        </div>

        <h2 class="text-lg font-medium">{{ __('Employee Lines') }}</h2>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Employee') }}</th>
                    <th class="text-end">{{ __('Hours') }}</th>
                    <th class="text-end">{{ __('Rate') }}</th>
                    <th class="text-end">{{ __('Gross') }}</th>
                    <th class="text-end">{{ __('Tax') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($payroll->lines as $line)
                    <tr>
                        <td>{{ $line->employee_name }}</td>
                        <td class="text-end">{{ $line->hours_worked }}</td>
                        <td class="text-end">{{ number_format($line->hourly_rate, 2) }}</td>
                        <td class="text-end">{{ number_format($line->gross_pay, 2) }}</td>
                        <td class="text-end">{{ number_format($line->tax_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($line->net_pay, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-4">{{ __('No lines yet.') }}</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>

        @if($payroll->isDraft())
            <div class="border-t pt-4">
                <h2 class="font-medium mb-2">{{ __('Add Employee') }}</h2>
                <form method="post" action="{{ route('dashboard.money.payroll.add-line', $payroll) }}" class="grid md:grid-cols-3 gap-2">
                    @csrf
                    <x-form.group>
                        <x-form.label>{{ __('Staff Profile') }}</x-form.label>
                        <x-form.select name="staff_profile_id" required>
                            <option value="">{{ __('Select…') }}</option>
                            @foreach($staffProfiles as $profile)
                                <option value="{{ $profile->id }}">{{ $profile->user?->name ?? "Staff #{$profile->id}" }}</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                    <x-form.group>
                        <x-form.label>{{ __('Timesheet') }}</x-form.label>
                        <x-form.select name="timesheet_submission_id">
                            <option value="">{{ __('None (salary)') }}</option>
                            @foreach($timesheets as $ts)
                                <option value="{{ $ts->id }}">{{ $ts->week_start?->format('Y-m-d') }} ({{ $ts->total_hours }}h)</option>
                            @endforeach
                        </x-form.select>
                    </x-form.group>
                    <x-form.group>
                        <x-form.label>{{ __('Tax') }}</x-form.label>
                        <x-form.input type="number" name="tax_amount" value="0" min="0" step="0.01" />
                    </x-form.group>
                    <div class="md:col-span-3">
                        <x-button type="submit">{{ __('Add Employee') }}</x-button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
