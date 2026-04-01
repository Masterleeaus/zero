@extends('panel.layout.app')
@section('title', __('Job Types'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-types.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Type') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $search ?? '' }}" placeholder="{{ __('Search types…') }}" />
            <div class="md:col-span-3 flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.job-types.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($types as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-types.show', $type) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.job-types.edit', $type) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-slate-500 py-6">{{ __('No job types found.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $types->links() }}
    </div>
@endsection
