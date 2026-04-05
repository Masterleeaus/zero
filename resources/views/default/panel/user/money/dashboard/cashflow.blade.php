@extends('panel.layout.app')
@section('title', __('Cash Flow'))

@section('content')
    <div class="py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ __('Cash Flow') }}</h1>

        {{-- Rolling 90-day totals --}}
        <div class="bg-white border rounded p-5">
            <h2 class="font-semibold text-gray-700 mb-3">{{ __('Rolling 90-Day Position') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Actual Inflow') }}</p>
                    <p class="text-lg font-semibold text-green-700">{{ number_format($rolling90['totals']['actual_inflow'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Actual Outflow') }}</p>
                    <p class="text-lg font-semibold text-red-600">{{ number_format($rolling90['totals']['actual_outflow'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Projected Inflow') }}</p>
                    <p class="text-lg font-semibold text-blue-700">{{ number_format($rolling90['totals']['projected_inflow'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Projected Outflow') }}</p>
                    <p class="text-lg font-semibold text-orange-600">{{ number_format($rolling90['totals']['projected_outflow'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Net Position') }}</p>
                    <p class="text-lg font-bold {{ $rolling90['totals']['net_position'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ number_format($rolling90['totals']['net_position'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Monthly projection --}}
        <div class="bg-white border rounded overflow-hidden">
            <h2 class="font-semibold text-gray-700 px-4 py-3 border-b">{{ __('Monthly Projection') }}</h2>
            <table class="w-full text-sm divide-y">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">{{ __('Month') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Actual In') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Actual Out') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Proj. In') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Proj. Out') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($monthly as $row)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $row['month'] }}</td>
                        <td class="px-4 py-2 text-right text-green-700">{{ number_format($row['actual_inflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-red-600">{{ number_format($row['actual_outflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-blue-700">{{ number_format($row['projected_inflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-orange-600">{{ number_format($row['projected_outflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold {{ $row['net_position'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($row['net_position'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Weekly projection --}}
        <div class="bg-white border rounded overflow-hidden">
            <h2 class="font-semibold text-gray-700 px-4 py-3 border-b">{{ __('Weekly Projection (Next 4 Weeks)') }}</h2>
            <table class="w-full text-sm divide-y">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">{{ __('Week') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('Period') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Actual In') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Proj. In') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Actual Out') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Proj. Out') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($weekly as $row)
                    <tr>
                        <td class="px-4 py-2">{{ __('Week') }} {{ $row['week'] }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $row['period_start'] }} – {{ $row['period_end'] }}</td>
                        <td class="px-4 py-2 text-right text-green-700">{{ number_format($row['actual_inflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-blue-700">{{ number_format($row['projected_inflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-red-600">{{ number_format($row['actual_outflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right text-orange-600">{{ number_format($row['projected_outflow'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold {{ $row['net_position'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($row['net_position'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
