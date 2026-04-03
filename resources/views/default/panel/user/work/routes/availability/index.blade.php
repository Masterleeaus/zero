@extends('panel.layout.app')
@section('title', __('Technician Availability'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.routes.availability.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Availability') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-select name="user_id" label="">
                <option value="">{{ __('All technicians') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </x-select>
            <x-select name="team_id" label="">
                <option value="">{{ __('All teams') }}</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected(($filters['team_id'] ?? '') == $team->id)>{{ $team->name }}</option>
                @endforeach
            </x-select>
            <x-select name="is_active" label="">
                <option value="">{{ __('Active / Inactive') }}</option>
                <option value="1" @selected(($filters['is_active'] ?? '') === '1')>{{ __('Active') }}</option>
                <option value="0" @selected(($filters['is_active'] ?? '') === '0')>{{ __('Inactive') }}</option>
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.routes.availability.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Technician') }}</th>
                    <th>{{ __('Schedule Name') }}</th>
                    <th>{{ __('Work Hours') }}</th>
                    <th>{{ __('Active') }}</th>
                    <th>{{ __('Valid Period') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($availabilities as $avail)
                    <tr>
                        <td>{{ $avail->user?->name ?? '—' }}</td>
                        <td>{{ $avail->name ?? '—' }}</td>
                        <td>{{ $avail->work_start_time ?? '—' }} – {{ $avail->work_end_time ?? '—' }}</td>
                        <td>
                            @if($avail->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('Yes') }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ __('No') }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $avail->valid_from ? \Carbon\Carbon::parse($avail->valid_from)->format('d M Y') : '—' }}
                            @if($avail->valid_until) – {{ \Carbon\Carbon::parse($avail->valid_until)->format('d M Y') }} @endif
                        </td>
                        <td class="text-end">
                            <x-button href="{{ route('dashboard.work.routes.availability.edit', $avail) }}" variant="ghost" size="sm">
                                {{ __('Edit') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">{{ __('No availability schedules found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $availabilities->links() }}
    </div>
@endsection
