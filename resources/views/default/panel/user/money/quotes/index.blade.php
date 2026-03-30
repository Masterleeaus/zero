@extends('panel.layout.app')
@section('title', __('Quotes'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.quotes.create') }}">
        <x-tabler-file-plus class="size-4" />
        {{ __('New Quote') }}
    </x-button>
    <x-button href="{{ route('dashboard.money.invoices.index') }}" variant="ghost">
        <x-tabler-file-invoice class="size-4" />
        {{ __('Invoices') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search quotes') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['draft', 'sent', 'accepted', 'rejected', 'expired'] as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.money.quotes.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Number') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Issued') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($quotes as $quote)
                    <tr>
                        <td>{{ $quote->quote_number }}</td>
                        <td>{{ $quote->customer?->name }}</td>
                        <td><x-badge variant="info">{{ ucfirst($quote->status) }}</x-badge></td>
                        <td>{{ $quote->total }}</td>
                        <td>{{ optional($quote->issue_date)->toFormattedDateString() }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.money.quotes.show', $quote) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">
                            {{ __('No quotes yet') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $quotes->links() }}
    </div>
@endsection
