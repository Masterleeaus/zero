@extends('panel.layout.app')
@section('title', __('Journal Entries'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.journal.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Journal Entry') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Lines') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($entries as $entry)
                    <tr>
                        <td class="font-mono text-sm">{{ $entry->reference ?? '—' }}</td>
                        <td>
                            <a href="{{ route('dashboard.money.journal.show', $entry) }}" class="text-blue-600 hover:underline">
                                {{ $entry->description }}
                            </a>
                        </td>
                        <td>{{ $entry->entry_date?->format('d M Y') }}</td>
                        <td>
                            @php
                                $variant = match ($entry->status) {
                                    'posted' => 'success',
                                    'void'   => 'danger',
                                    default  => 'warning',
                                };
                            @endphp
                            <x-badge variant="{{ $variant }}">{{ ucfirst($entry->status) }}</x-badge>
                        </td>
                        <td>{{ $entry->lines->count() }}</td>
                        <td class="text-end">
                            <x-button href="{{ route('dashboard.money.journal.show', $entry) }}" variant="ghost" size="sm">
                                {{ __('View') }}
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-4">{{ __('No journal entries') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $entries->links() }}
    </div>
@endsection
