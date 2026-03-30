@extends('panel.layout.app')
@section('title', __('Service Agreements'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ __('Service Agreements') }}</h2>
            <x-button href="{{ route('dashboard.work.agreements.create') }}">
                {{ __('Create') }}
                <x-tabler-plus class="size-4" />
            </x-button>
        </div>

        <x-card>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Site') }}</th>
                        <th>{{ __('Frequency') }}</th>
                        <th>{{ __('Next Run') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td><a class="text-primary-600" href="{{ route('dashboard.work.agreements.show', $agreement) }}">{{ $agreement->title }}</a></td>
                            <td>{{ $agreement->customer?->name ?? '—' }}</td>
                            <td>{{ $agreement->site?->name ?? '—' }}</td>
                            <td>{{ __($agreement->frequency) }}</td>
                            <td>{{ $agreement->next_run_at?->format('Y-m-d') ?? '—' }}</td>
                            <td><x-badge>{{ __($agreement->status) }}</x-badge></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-500">{{ __('No agreements yet.') }}</td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-table>
            <div class="mt-4">
                {{ $agreements->links() }}
            </div>
        </x-card>
    </div>
@endsection
