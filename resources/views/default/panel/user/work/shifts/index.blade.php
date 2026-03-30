@extends('panel.layout.app')
@section('title', __('Shifts'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Shifts') }}</h1>
            <x-button href="{{ route('dashboard.work.shifts.create') }}">{{ __('New Shift') }}</x-button>
        </div>
        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Service Job') }}</th>
                    <th>{{ __('Start') }}</th>
                    <th>{{ __('End') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $shift->user?->name }}</td>
                        <td>{{ $shift->serviceJob?->title ?? '-' }}</td>
                        <td>{{ $shift->start_at }}</td>
                        <td>{{ $shift->end_at }}</td>
                        <td><x-badge>{{ __($shift->status) }}</x-badge></td>
                        <td class="text-end">
                            <x-button size="sm" href="{{ route('dashboard.work.shifts.show', $shift) }}">{{ __('View') }}</x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500">{{ __('No shifts found') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>
        {{ $shifts->links() }}
    </div>
@endsection
