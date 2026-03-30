@extends('panel.layout.app')
@section('title', __('Service Job'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.service-jobs.edit', $job) }}">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('Title') }}</div>
                    <div class="text-lg font-semibold">{{ $job->title }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="info">{{ ucfirst($job->status) }}</x-badge>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Site') }}</div>
                    <div>{{ $job->site?->name }}</div>
                </div>
                @if($job->customer)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                        <div>{{ $job->customer?->name }}</div>
                    </div>
                @endif
                @if($job->scheduled_at)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Scheduled At') }}</div>
                        <div>{{ $job->scheduled_at?->format('Y-m-d H:i') }}</div>
                    </div>
                @endif
            </div>

            @if($job->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $job->notes }}</p>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">{{ __('Checklist') }}</div>
                <x-button size="xs" href="{{ route('dashboard.work.checklists.create', ['job_id' => $job->id]) }}">
                    <x-tabler-plus class="size-4" /> {{ __('Add Item') }}
                </x-button>
            </div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Completed') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($job->checklists as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>
                                @if($item->is_completed)
                                    <x-badge variant="success">{{ __('Done') }}</x-badge>
                                @else
                                    <x-badge variant="warning">{{ __('Pending') }}</x-badge>
                                @endif
                            </td>
                            <td class="text-end whitespace-nowrap">
                                <x-button variant="ghost-shadow" size="none" class="size-9"
                                          href="{{ route('dashboard.work.checklists.show', $item) }}">
                                    <x-tabler-eye class="size-4" />
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-slate-500 py-4">{{ __('No checklist items') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </x-card>
    </div>
@endsection
