@extends('panel.layout.app')
@section('title', __('Job Templates'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-templates.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Template') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search templates…') }}" />
            <x-select name="job_type_id">
                <option value="">{{ __('All types') }}</option>
                @foreach($jobTypes as $jt)
                    <option value="{{ $jt->id }}" @selected(($filters['job_type_id'] ?? '') == $jt->id)>{{ $jt->name }}</option>
                @endforeach
            </x-select>
            <div class="md:col-span-2 flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.job-templates.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Team') }}</th>
                    <th>{{ __('Duration (h)') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td>{{ optional($template->jobType)->name ?? '–' }}</td>
                        <td>{{ optional($template->team)->name ?? '–' }}</td>
                        <td>{{ $template->duration }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-templates.show', $template) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-templates.edit', $template) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">{{ __('No job templates found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $templates->links() }}
    </div>
@endsection
