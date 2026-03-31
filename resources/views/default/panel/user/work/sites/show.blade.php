@extends('panel.layout.app')
@section('title', __('work.labels.site'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.sites.edit', $site) }}">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-slate-500">{{ __('work.sites.table_name') }}</div>
                    <div class="text-lg font-semibold">{{ $site->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500">{{ __('Status') }}</div>
                    <x-badge variant="info">{{ ucfirst($site->status) }}</x-badge>
                </div>
                @if($site->reference)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('work.sites.table_reference') }}</div>
                        <div>{{ $site->reference }}</div>
                    </div>
                @endif
                @if($site->address)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Address') }}</div>
                        <div>{{ $site->address }}</div>
                    </div>
                @endif
                @if($site->start_date)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Start Date') }}</div>
                        <div>{{ $site->start_date?->toFormattedDateString() }}</div>
                    </div>
                @endif
                @if($site->deadline)
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Deadline') }}</div>
                        <div>{{ $site->deadline?->toFormattedDateString() }}</div>
                    </div>
                @endif
            </div>

            @if($site->notes)
                <div class="mt-4">
                    <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
                    <p class="whitespace-pre-line">{{ $site->notes }}</p>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">{{ __('work.jobs.title') }}</div>
                <x-button size="xs" href="{{ route('dashboard.work.service-jobs.create', ['site_id' => $site->id]) }}">
                    <x-tabler-plus class="size-4" /> {{ __('work.jobs.add') }}
                </x-button>
            </div>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('work.jobs.table_title') }}</th>
                        <th>{{ __('work.jobs.table_status') }}</th>
                        <th>{{ __('work.jobs.table_scheduled') }}</th>
                        <th class="text-end">{{ __('work.jobs.table_action') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($site->jobs as $job)
                        <tr>
                            <td>{{ $job->title }}</td>
                            <td><x-badge variant="info">{{ ucfirst($job->status) }}</x-badge></td>
                            <td>{{ optional($job->scheduled_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end whitespace-nowrap">
                                <x-button variant="ghost-shadow" size="none" class="size-9"
                                          href="{{ route('dashboard.work.service-jobs.show', $job) }}">
                                    <x-tabler-eye class="size-4" />
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-500 py-4">{{ __('work.jobs.empty') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
        </x-card>
    </div>
@endsection
