@extends('panel.layout.app')
@section('title', __('Job Profitability'))

@section('content')
    <div class="py-6 space-y-4">
        <h1 class="text-xl font-semibold">{{ __('Job Profitability') }}</h1>

        <form method="get" class="flex gap-2 items-end flex-wrap">
            <x-form.group>
                <x-form.label for="period_start">{{ __('From') }}</x-form.label>
                <x-form.input type="date" id="period_start" name="period_start" value="{{ $periodStart }}" />
            </x-form.group>
            <x-form.group>
                <x-form.label for="period_end">{{ __('To') }}</x-form.label>
                <x-form.input type="date" id="period_end" name="period_end" value="{{ $periodEnd }}" />
            </x-form.group>
            <x-button type="submit">{{ __('Filter') }}</x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Job ID') }}</th>
                    <th class="text-end">{{ __('Revenue') }}</th>
                    <th class="text-end">{{ __('Cost') }}</th>
                    <th class="text-end">{{ __('Margin %') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($rows as $row)
                    <tr>
                        <td>#{{ $row['service_job_id'] }}</td>
                        <td class="text-end">{{ number_format($row['revenue'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['cost'], 2) }}</td>
                        <td class="text-end {{ $row['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $row['margin'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-gray-500 py-4">{{ __('No job costing data found.') }}</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </div>
@endsection
