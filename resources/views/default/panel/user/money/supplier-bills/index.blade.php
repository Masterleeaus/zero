@extends('panel.layout.app')
@section('title', __('Supplier Bills'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.supplier-bills.create') }}">
        <x-tabler-file-text class="size-4" />
        {{ __('New Bill') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search reference') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['draft','awaiting_payment','partial','paid','overdue','void'] as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.money.supplier-bills.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Supplier') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            @forelse($bills as $bill)
                <tr>
                    <td>
                        <a href="{{ route('dashboard.money.supplier-bills.show', $bill) }}" class="font-medium hover:underline">
                            {{ $bill->reference ?: 'BILL-' . $bill->id }}
                        </a>
                    </td>
                    <td>{{ $bill->supplier?->name }}</td>
                    <td>{{ optional($bill->bill_date)->toFormattedDateString() }}</td>
                    <td>{{ optional($bill->due_date)->toFormattedDateString() ?: '—' }}</td>
                    <td>
                        <x-badge variant="{{ match($bill->status) {
                            'paid'     => 'success',
                            'overdue'  => 'danger',
                            'awaiting_payment', 'partial' => 'warning',
                            default    => 'secondary',
                        } }}">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</x-badge>
                    </td>
                    <td>{{ number_format($bill->total, 2) }} {{ $bill->currency }}</td>
                    <td>{{ number_format($bill->balance, 2) }}</td>
                    <td>
                        <x-button href="{{ route('dashboard.money.supplier-bills.edit', $bill) }}" size="sm" variant="ghost">
                            {{ __('Edit') }}
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-slate-400 py-8">{{ __('No supplier bills found.') }}</td>
                </tr>
            @endforelse
        </x-table>

        {{ $bills->links() }}
    </div>
@endsection
