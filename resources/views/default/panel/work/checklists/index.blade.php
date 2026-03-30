@extends('panel.layout.app')
@section('title', __('Checklists'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.checklists.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Checklist Item') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search checklist items') }}" />
            <x-select name="job_id">
                <option value="">{{ __('All jobs') }}</option>
                @foreach($jobs as $job)
                    <option value="{{ $job->id }}" @selected(($filters['job_id'] ?? '') == $job->id)>{{ $job->title }}</option>
                @endforeach
            </x-select>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="pending_only" value="1" @checked($filters['pending_only'] ?? false)>
                <span>{{ __('Pending only') }}</span>
            </label>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.checklists.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Job') }}</th>
                    <th>{{ __('Site') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($checklists as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->job?->title }}</td>
                        <td>{{ $item->job?->site?->name }}</td>
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
                        <td colspan="5" class="text-center text-slate-500 py-6">
                            {{ __('No checklist items yet') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $checklists->links() }}
    </div>
@endsection
