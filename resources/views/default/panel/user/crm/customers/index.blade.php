@extends('panel.layout.app')
@section('title', __('crm.customers.title'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.crm.customers.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('crm.customers.new') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <form method="get" class="flex gap-3">
            <x-input name="q" value="{{ $search }}" placeholder="{{ __('crm.customers.search') }}" />
            <x-button type="submit" variant="secondary">
                <x-tabler-search class="size-4" />
                {{ __('Search') }}
            </x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('crm.customers.name') }}</th>
                    <th>{{ __('crm.customers.email') }}</th>
                    <th>{{ __('crm.customers.phone') }}</th>
                    <th>{{ __('crm.customers.status') }}</th>
                    <th class="text-end">{{ __('crm.customers.action') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>
                            <x-badge variant="info">{{ ucfirst($customer->status) }}</x-badge>
                        </td>
                        <td class="text-end whitespace-nowrap">
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.crm.customers.show', $customer) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost-shadow" size="none" class="size-9"
                                      href="{{ route('dashboard.crm.customers.edit', $customer) }}">
                                <x-tabler-pencil class="size-4" />
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-6">
                            {{ __('crm.customers.empty') }}
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $customers->links() }}
    </div>
@endsection
