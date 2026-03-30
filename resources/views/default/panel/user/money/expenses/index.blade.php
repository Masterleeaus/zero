@extends('panel.layout.app')
@section('title', __('Expenses'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Expenses') }}</h1>
            <x-button href="{{ route('dashboard.money.expenses.create') }}">{{ __('New Expense') }}</x-button>
        </div>

        <form method="get" class="grid md:grid-cols-3 gap-4">
            <x-form.group>
                <x-form.label for="start_date">{{ __('Start date') }}</x-form.label>
                <x-form.input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" />
            </x-form.group>
            <x-form.group>
                <x-form.label for="end_date">{{ __('End date') }}</x-form.label>
                <x-form.input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" />
            </x-form.group>
            <div class="flex items-end">
                <x-button type="submit">{{ __('Filter') }}</x-button>
            </div>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->title }}</td>
                        <td>{{ $expense->category?->name ?? __('Uncategorised') }}</td>
                        <td>{{ $expense->expense_date?->format('Y-m-d') }}</td>
                        <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                        <td class="text-end">
                            <x-button size="sm" variant="secondary" href="{{ route('dashboard.money.expenses.edit', $expense) }}">{{ __('Edit') }}</x-button>
                            <form method="post" action="{{ route('dashboard.money.expenses.destroy', $expense) }}" class="inline">
                                @csrf
                                @method('delete')
                                <x-button size="sm" variant="danger" type="submit">{{ __('Delete') }}</x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500">{{ __('No expenses found') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $expenses->links() }}
    </div>
@endsection
