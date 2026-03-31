@extends('panel.layout.app')
@section('title', __('work.jobs.title'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.service-jobs.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('work.jobs.new') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('work.jobs.search') }}" />
            <x-select name="status">
                <option value="">{{ __('work.jobs.filter_statuses') }}</option>
                @foreach(['scheduled', 'in-progress', 'completed', 'cancelled'] as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <x-select name="site_id">
                <option value="">{{ __('work.jobs.filter_sites') }}</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" @selected(($filters['site_id'] ?? '') == $site->id)>{{ $site->name }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.service-jobs.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('work.jobs.table_title') }}</th>
                    <th>{{ __('work.jobs.table_site') }}</th>
                    <th>{{ __('work.jobs.table_status') }}</th>
                    <th>{{ __('work.jobs.table_scheduled') }}</th>
                    <th class="text-end">{{ __('work.jobs.table_action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($jobs as $job)
                    <tr>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->site?->name }}</td>
                        <td><x-badge variant="info">{{ ucfirst($job->status) }}</x-badge></td>
                        <td>{{ optional($job->scheduled_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.service-jobs.show', $job) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.service-jobs.edit', $job) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">
                            {{ __('work.jobs.empty') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $jobs->links() }}
    </div>
@endsection
