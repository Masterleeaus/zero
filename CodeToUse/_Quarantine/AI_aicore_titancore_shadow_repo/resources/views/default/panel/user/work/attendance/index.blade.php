@extends('panel.layout.app')
@section('title', __('work.attendance.title'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ __('work.attendance.title') }}</h2>
            <x-button href="{{ route('dashboard.work.attendance.create') }}">
                {{ __('work.attendance.check_in') }}
                <x-tabler-plus class="size-4" />
            </x-button>
        </div>

        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('work.attendance.job') }}</th>
                        <th>{{ __('work.attendance.check_in') }}</th>
                        <th>{{ __('work.attendance.check_out') }}</th>
                        <th>{{ __('work.attendance.duration') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->serviceJob?->title ?? __('work.jobs.unassigned') }}</td>
                            <td>{{ $attendance->check_in_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $attendance->check_out_at?->format('Y-m-d H:i') ?? __('work.attendance.open') }}</td>
                            <td>{{ $attendance->duration_minutes ?? '—' }}</td>
                            <td class="text-end">
                                @if(! $attendance->check_out_at)
                                    <form method="post" action="{{ route('dashboard.work.attendance.checkout', $attendance) }}">
                                        @csrf
                                        <x-button size="sm" type="submit">{{ __('work.attendance.check_out') }}</x-button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-500">{{ __('work.attendance.empty') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
            <div class="mt-4">
                {{ $attendances->links() }}
            </div>
        </x-card>
    </div>
@endsection
