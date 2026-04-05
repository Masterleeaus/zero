@extends('panel.layout.app')
@section('title', __('Financial Recommendations'))

@section('content')
    <div class="py-6 space-y-4">
        <h1 class="text-xl font-semibold">{{ __('Financial Action Recommendations') }}</h1>

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Title') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Type') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Severity') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Created') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($queue as $rec)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $rec->title }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $rec->action_type }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $rec->severity === 'critical' ? 'bg-red-100 text-red-700' : ($rec->severity === 'high' ? 'bg-orange-100 text-orange-700' : ($rec->severity === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                                    {{ ucfirst($rec->severity) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ $rec->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('dashboard.money.recommendations.review', $rec) }}" class="text-blue-600 hover:underline text-xs">{{ __('Review') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">{{ __('No pending recommendations.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
