@extends('panel.layout.app')
@section('title', __('Expense Categories'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Expense Categories') }}</h1>
            <x-button href="{{ route('dashboard.money.expense-categories.create') }}">{{ __('New Category') }}</x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description }}</td>
                        <td class="text-end space-x-2">
                            <x-button size="sm" variant="secondary" href="{{ route('dashboard.money.expense-categories.edit', $category) }}">{{ __('Edit') }}</x-button>
                            <form method="post" action="{{ route('dashboard.money.expense-categories.destroy', $category) }}" class="inline">
                                @csrf
                                @method('delete')
                                <x-button size="sm" variant="danger" type="submit">{{ __('Delete') }}</x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500">{{ __('No categories found') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $categories->links() }}
    </div>
@endsection
