@extends('panel.layout.app')
@section('title', __('Budgets'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Budgets') }}</h1>
        </div>

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-medium mb-4">{{ __('Create Budget') }}</h2>
            <form method="POST" action="{{ route('dashboard.money.budgets.store') }}" class="grid md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Period Type') }}</label>
                    <select name="period_type" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="quarterly">{{ __('Quarterly') }}</option>
                        <option value="yearly">{{ __('Yearly') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Starts At') }}</label>
                    <input type="date" name="starts_at" class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Ends At') }}</label>
                    <input type="date" name="ends_at" class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">{{ __('Save Budget') }}</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Name') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Period') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Dates') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($budgets as $budget)
                        <tr>
                            <td class="px-4 py-2">{{ $budget->name }}</td>
                            <td class="px-4 py-2">{{ ucfirst($budget->period_type) }}</td>
                            <td class="px-4 py-2">{{ $budget->starts_at->format('d M Y') }} – {{ $budget->ends_at->format('d M Y') }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $budget->status === 'active' ? 'bg-green-100 text-green-700' : ($budget->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($budget->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">{{ __('No budgets yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
