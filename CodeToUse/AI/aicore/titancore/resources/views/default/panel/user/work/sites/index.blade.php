@extends('panel.layout.app')
@section('title', __('work.sites.title'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.sites.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('work.sites.new') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <x-input name="q" value="{{ $search ?? '' }}" placeholder="{{ __('work.sites.search') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['active', 'on-hold', 'completed', 'cancelled'] as $option)
                    <option value="{{ $option }}" @selected(($status ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <div class="md:col-span-2 flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.work.sites.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('work.sites.table_name') }}</th>
                    <th>{{ __('work.sites.table_reference') }}</th>
                    <th>{{ __('work.sites.table_status') }}</th>
                    <th>{{ __('work.sites.timeline') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($sites as $site)
                    <tr>
                        <td>{{ $site->name }}</td>
                        <td>{{ $site->reference }}</td>
                        <td><x-badge variant="info">{{ ucfirst($site->status) }}</x-badge></td>
                        <td>
                            @if($site->start_date)
                                {{ $site->start_date?->toFormattedDateString() }}
                                @if($site->deadline)
                                    – {{ $site->deadline?->toFormattedDateString() }}
                                @endif
                            @endif
                        </td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.sites.show', $site) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.work.sites.edit', $site) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">
                            {{ __('work.sites.empty') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $sites->links() }}
    </div>
@endsection
