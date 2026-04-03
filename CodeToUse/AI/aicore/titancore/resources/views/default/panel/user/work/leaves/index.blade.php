@extends('panel.layout.app')
@section('title', __('Leaves'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Leaves') }}</h1>
            <x-button href="{{ route('dashboard.work.leaves.create') }}">{{ __('New Leave') }}</x-button>
        </div>
        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Start') }}</th>
                    <th>{{ __('End') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->user?->name }}</td>
                        <td>{{ ucfirst($leave->type) }}</td>
                        <td>{{ $leave->start_date?->format('Y-m-d') }}</td>
                        <td>{{ $leave->end_date?->format('Y-m-d') }}</td>
                        <td><x-badge>{{ __($leave->status) }}</x-badge></td>
                        <td class="text-end space-x-2">
                            <x-button size="sm" href="{{ route('dashboard.work.leaves.show', $leave) }}">{{ __('View') }}</x-button>
                            <x-button size="sm" variant="secondary" href="{{ route('dashboard.work.leaves.edit', $leave) }}">{{ __('Edit') }}</x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500">{{ __('No leave records') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>
        {{ $leaves->links() }}
    </div>
@endsection
