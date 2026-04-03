@extends('panel.layout.app')
@section('title', __('Routes'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.routes.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Route') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search routes…') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach($statuses as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <x-select name="team_id">
                <option value="">{{ __('All teams') }}</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected(($filters['team_id'] ?? '') == $team->id)>{{ $team->name }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.routes.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Technician') }}</th>
                    <th>{{ __('Team') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Max Stops/Day') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($routes as $route)
                    <tr>
                        <td>
                            <a href="{{ route('dashboard.work.routes.show', $route) }}" class="font-medium hover:underline">
                                {{ $route->name }}
                            </a>
                        </td>
                        <td>{{ $route->assignedUser?->name ?? '—' }}</td>
                        <td>{{ $route->team?->name ?? '—' }}</td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $route->status === 'active' ? 'bg-green-100 text-green-800' : ($route->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">
                                {{ ucfirst($route->status) }}
                            </span>
                        </td>
                        <td>{{ $route->max_stops_per_day ?: __('Unlimited') }}</td>
                        <td class="text-end">
                            <x-button href="{{ route('dashboard.work.routes.edit', $route) }}" variant="ghost" size="sm">
                                {{ __('Edit') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">{{ __('No routes found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $routes->links() }}
    </div>
@endsection
