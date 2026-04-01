@extends('panel.layout.app')
@section('title', __('Job Stages'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-stages.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Stage') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search stages…') }}" />
            <x-select name="stage_type">
                <option value="">{{ __('All types') }}</option>
                @foreach($stageTypes as $t)
                    <option value="{{ $t }}" @selected(($filters['stage_type'] ?? '') === $t)>{{ ucfirst($t) }}</option>
                @endforeach
            </x-select>
            <div class="md:col-span-2 flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.job-stages.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Sequence') }}</th>
                    <th>{{ __('Default') }}</th>
                    <th>{{ __('Closed') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($stages as $stage)
                    <tr>
                        <td>
                            <span class="inline-block size-3 rounded-full mr-2" style="background:{{ $stage->color }}"></span>
                            {{ $stage->name }}
                        </td>
                        <td><x-badge variant="info">{{ ucfirst($stage->stage_type) }}</x-badge></td>
                        <td>{{ $stage->sequence }}</td>
                        <td>{{ $stage->is_default ? __('Yes') : '–' }}</td>
                        <td>{{ $stage->is_closed ? __('Yes') : '–' }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-stages.show', $stage) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-stages.edit', $stage) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">{{ __('No job stages found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $stages->links() }}
    </div>
@endsection
