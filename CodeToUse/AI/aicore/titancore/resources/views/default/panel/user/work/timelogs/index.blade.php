@extends('panel.layout.app')
@section('title', __('Timelogs'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">{{ __('Timelogs') }}</h2>
            <x-button href="{{ route('dashboard.work.timelogs.create') }}">
                {{ __('Add timelog') }}
                <x-tabler-plus class="size-4" />
            </x-button>
        </div>

        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('work.labels.service_job') }}</th>
                        <th>{{ __('Started') }}</th>
                        <th>{{ __('Ended') }}</th>
                        <th>{{ __('Duration (min)') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($timelogs as $log)
                        <tr>
                            <td>{{ $log->serviceJob?->title ?? __('work.jobs.unassigned') }}</td>
                            <td>{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->ended_at?->format('Y-m-d H:i') ?? __('Running') }}</td>
                            <td>{{ $log->duration_minutes ?? '—' }}</td>
                            <td class="text-end">
                                @if(! $log->ended_at)
                                    <form method="post" action="{{ route('dashboard.work.timelogs.stop', $log) }}">
                                        @csrf
                                        <x-button size="sm" type="submit">{{ __('Stop') }}</x-button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-4">{{ __('No timelogs yet.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
            <div class="mt-4">
                {{ $timelogs->links() }}
            </div>
        </x-card>
    </div>
@endsection
