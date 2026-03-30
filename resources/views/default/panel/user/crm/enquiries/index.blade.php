@extends('panel.layout.app')
@section('title', __('Enquiries'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.crm.enquiries.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Enquiry') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="flex gap-3">
            <x-input name="q" value="{{ $search }}" placeholder="{{ __('Search enquiries') }}" />
            <x-button type="submit" variant="secondary">
                <x-tabler-search class="size-4" />
                {{ __('Search') }}
            </x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>{{ $enquiry->name }}</td>
                        <td>{{ $enquiry->customer?->name }}</td>
                        <td><x-badge variant="info">{{ ucfirst($enquiry->status) }}</x-badge></td>
                        <td>{{ $enquiry->source }}</td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.crm.enquiries.show', $enquiry) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">
                            {{ __('No enquiries yet') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $enquiries->links() }}
    </div>
@endsection
