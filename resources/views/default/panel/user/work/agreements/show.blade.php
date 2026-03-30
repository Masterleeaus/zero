@extends('panel.layout.app')
@section('title', $agreement->title)

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="flex justify-between">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Frequency') }}</div>
                    <div class="font-semibold">{{ __($agreement->frequency) }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Next run') }}</div>
                    <div class="font-semibold">{{ $agreement->next_run_at?->format('Y-m-d') ?? '—' }}</div>
                </div>
                <div>
                    <x-badge>{{ __($agreement->status) }}</x-badge>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                    <div>{{ $agreement->customer?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Site') }}</div>
                    <div>{{ $agreement->site?->name ?? '—' }}</div>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="font-semibold mb-2">{{ __('Linked Jobs') }}</div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Scheduled at') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($jobs as $job)
                        <tr>
                            <td>{{ $job->title }}</td>
                            <td>{{ $job->scheduled_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ __($job->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-center text-slate-500">{{ __('No jobs linked yet.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </x-card>
    </div>
@endsection
