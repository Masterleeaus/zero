@extends('panel.layout.app')
@section('title', __('Attendance'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ __('Attendance') }}</h2>
            <x-button href="{{ route('dashboard.work.attendance.create') }}">
                {{ __('Check in') }}
                <x-tabler-plus class="size-4" />
            </x-button>
        </div>

        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Check in') }}</th>
                        <th>{{ __('Check out') }}</th>
                        <th>{{ __('Duration (min)') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->serviceJob?->title ?? __('Unassigned') }}</td>
                            <td>{{ $attendance->check_in_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $attendance->check_out_at?->format('Y-m-d H:i') ?? __('Open') }}</td>
                            <td>{{ $attendance->duration_minutes ?? '—' }}</td>
                            <td class="text-end">
                                @if(! $attendance->check_out_at)
                                    <form method="post" action="{{ route('dashboard.work.attendance.checkout', $attendance) }}">
                                        @csrf
                                        <x-button size="sm" type="submit">{{ __('Check out') }}</x-button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-500">{{ __('No attendance yet.') }}</td>
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
