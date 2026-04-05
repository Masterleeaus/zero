@extends('panel.layout.app')
@section('title', __('Budget Variance'))

@section('content')
    <div class="py-6 space-y-4">
        <h1 class="text-xl font-semibold">{{ __('Budget Variance Report') }}</h1>

        @if(empty($report['budgets']))
            <div class="bg-white rounded shadow p-6 text-center text-gray-400">{{ __('No active budgets found.') }}</div>
        @else
            @foreach($report['budgets'] as $b)
                <div class="bg-white rounded shadow p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <h2 class="font-medium">{{ $b['budget_name'] }}</h2>
                        <span class="text-xs text-gray-500">{{ $b['period']['from'] }} — {{ $b['period']['to'] }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div><span class="text-gray-500">{{ __('Budget') }}</span><div class="font-semibold">${{ number_format($b['total_budget'], 2) }}</div></div>
                        <div><span class="text-gray-500">{{ __('Actual') }}</span><div class="font-semibold">${{ number_format($b['total_actual'], 2) }}</div></div>
                        <div><span class="text-gray-500">{{ __('Variance') }}</span>
                            <div class="font-semibold {{ $b['total_variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ${{ number_format($b['total_variance'], 2) }}
                                @if($b['total_variance_pct'] !== null)
                                    ({{ $b['total_variance_pct'] }}%)
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(!empty($b['lines']))
                        <table class="min-w-full text-xs divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-1 text-left text-gray-600">{{ __('Type') }}</th>
                                    <th class="px-3 py-1 text-right text-gray-600">{{ __('Budget') }}</th>
                                    <th class="px-3 py-1 text-right text-gray-600">{{ __('Actual') }}</th>
                                    <th class="px-3 py-1 text-right text-gray-600">{{ __('Variance %') }}</th>
                                    <th class="px-3 py-1 text-left text-gray-600">{{ __('Risk') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($b['lines'] as $line)
                                    <tr>
                                        <td class="px-3 py-1">{{ ucfirst($line['line_type']) }}</td>
                                        <td class="px-3 py-1 text-right">${{ number_format($line['budget'], 2) }}</td>
                                        <td class="px-3 py-1 text-right">${{ number_format($line['actual'], 2) }}</td>
                                        <td class="px-3 py-1 text-right">{{ $line['variance_percent'] ?? '—' }}%</td>
                                        <td class="px-3 py-1">
                                            <span class="px-1.5 py-0.5 rounded text-xs
                                                {{ in_array($line['risk'], ['high','critical']) ? 'bg-red-100 text-red-700' : ($line['risk'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                                {{ $line['risk'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endsection
