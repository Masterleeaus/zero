@extends('panel.layout.app')
@section('title', __('Scenario Simulation'))

@section('content')
    <div class="py-6 space-y-4">
        <h1 class="text-xl font-semibold">{{ __('Financial Scenario Simulation') }}</h1>

        <div class="bg-white rounded shadow p-4">
            <form method="POST" action="{{ route('dashboard.money.scenarios.store') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Scenario Type') }}</label>
                        <select name="scenario_type" class="w-full border rounded px-3 py-2 text-sm" required>
                            @foreach($scenarioTypes as $type)
                                <option value="{{ $type }}" {{ old('scenario_type') === $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Horizon (days)') }}</label>
                        <input type="number" name="horizon_days" value="{{ old('horizon_days', 90) }}" min="7" max="365" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">{{ __('Run Simulation') }}</button>
                </div>
            </form>
        </div>

        @if($result)
            <div class="bg-white rounded shadow p-4 space-y-4">
                <h2 class="font-medium">{{ __('Simulation Result') }}: {{ ucwords(str_replace('_', ' ', $result['scenario_type'])) }}</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-600 mb-2">{{ __('Baseline') }}</h3>
                        <dl class="space-y-1 text-sm">
                            @foreach(['projected_revenue','projected_costs','projected_margin','margin_pct'] as $key)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="font-medium">{{ is_numeric($result['baseline'][$key]) ? '$'.number_format($result['baseline'][$key], 2) : $result['baseline'][$key] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600 mb-2">{{ __('Adjusted') }}</h3>
                        <dl class="space-y-1 text-sm">
                            @foreach(['projected_revenue','projected_costs','projected_margin','margin_pct'] as $key)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="font-medium">{{ is_numeric($result['adjusted'][$key]) ? '$'.number_format($result['adjusted'][$key], 2) : $result['adjusted'][$key] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                <div class="border-t pt-3">
                    <h3 class="text-sm font-medium text-gray-600 mb-2">{{ __('Impact') }}</h3>
                    <div class="flex gap-6 text-sm">
                        <div><span class="text-gray-500">{{ __('Revenue Impact') }}:</span> <span class="{{ $result['impact']['revenue_impact'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">${{ number_format($result['impact']['revenue_impact'], 2) }}</span></div>
                        <div><span class="text-gray-500">{{ __('Margin Impact') }}:</span> <span class="{{ $result['impact']['margin_impact'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">${{ number_format($result['impact']['margin_impact'], 2) }}</span></div>
                        <div><span class="text-gray-500">{{ __('Severity') }}:</span> <span class="font-medium uppercase">{{ $result['impact']['severity'] }}</span></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
