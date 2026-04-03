@extends('panel.layout.app')
@section('title', __('Suppliers'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.suppliers.create') }}">
        <x-tabler-building-store class="size-4" />
        {{ __('New Supplier') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="grid md:grid-cols-3 gap-3">
            <x-input name="q" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search suppliers') }}" />
            <x-select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(($filters['status'] ?? '') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </x-select>
            <div class="flex gap-3">
                <x-button type="submit" variant="secondary">
                    <x-tabler-search class="size-4" />
                    {{ __('Filter') }}
                </x-button>
                <x-button href="{{ route('dashboard.money.suppliers.index') }}" variant="ghost">
                    {{ __('Reset') }}
                </x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Payment Terms') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </x-slot:head>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <a href="{{ route('dashboard.money.suppliers.show', $supplier) }}" class="font-medium hover:underline">
                            {{ $supplier->name }}
                        </a>
                    </td>
                    <td>{{ $supplier->email }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->payment_terms }}</td>
                    <td><x-badge variant="{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($supplier->status) }}</x-badge></td>
                    <td>
                        <x-button href="{{ route('dashboard.money.suppliers.edit', $supplier) }}" size="sm" variant="ghost">
                            {{ __('Edit') }}
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-8">{{ __('No suppliers found.') }}</td>
                </tr>
            @endforelse
        </x-table>

        {{ $suppliers->links() }}
    </div>
@endsection
