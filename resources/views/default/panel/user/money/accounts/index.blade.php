@extends('panel.layout.app')
@section('title', __('Chart of Accounts'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.accounts.create') }}">
        <x-tabler-plus class="size-4" />
        {{ __('New Account') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">
        @foreach($accounts as $type => $group)
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">
                    {{ ucfirst($type) }}
                </h2>
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </x-slot:head>
                    <x-slot:body>
                        @forelse($group as $account)
                            <tr>
                                <td class="font-mono text-sm">{{ $account->code }}</td>
                                <td>
                                    <a href="{{ route('dashboard.money.accounts.show', $account) }}" class="text-blue-600 hover:underline">
                                        {{ $account->name }}
                                    </a>
                                </td>
                                <td class="text-sm text-gray-500">{{ $account->description }}</td>
                                <td>
                                    <x-badge variant="{{ $account->is_active ? 'success' : 'secondary' }}">
                                        {{ $account->is_active ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                <td class="text-end">
                                    <x-button href="{{ route('dashboard.money.accounts.edit', $account) }}" variant="ghost" size="sm">
                                        {{ __('Edit') }}
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-4">{{ __('No accounts') }}</td>
                            </tr>
                        @endforelse
                    </x-slot:body>
                </x-table>
            </div>
        @endforeach
    </div>
@endsection
