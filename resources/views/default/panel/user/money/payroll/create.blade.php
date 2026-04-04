@extends('panel.layout.app')
@section('title', __('New Payroll Run'))

@section('content')
    <div class="py-6 max-w-xl">
        <h1 class="text-xl font-semibold mb-4">{{ __('New Payroll Run') }}</h1>

        <form method="post" action="{{ route('dashboard.money.payroll.store') }}" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <x-form.group>
                    <x-form.label for="period_start">{{ __('Period Start') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.input type="date" id="period_start" name="period_start" value="{{ old('period_start') }}" required />
                    <x-form.error field="period_start" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="period_end">{{ __('Period End') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.input type="date" id="period_end" name="period_end" value="{{ old('period_end') }}" required />
                    <x-form.error field="period_end" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="pay_date">{{ __('Pay Date') }} <span class="text-red-500">*</span></x-form.label>
                    <x-form.input type="date" id="pay_date" name="pay_date" value="{{ old('pay_date') }}" required />
                    <x-form.error field="pay_date" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="reference">{{ __('Reference') }}</x-form.label>
                    <x-form.input id="reference" name="reference" value="{{ old('reference') }}" />
                    <x-form.error field="reference" />
                </x-form.group>
            </div>

            <x-form.group>
                <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
                <x-form.textarea id="notes" name="notes">{{ old('notes') }}</x-form.textarea>
            </x-form.group>

            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create Payroll Run') }}</x-button>
                <x-button variant="secondary" href="{{ route('dashboard.money.payroll.index') }}">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>
@endsection
