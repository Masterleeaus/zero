@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Bank Accounts') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Settlement accounts') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.money.bank-accounts.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('Add Account') }}
            </x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Bank') }}</th>
                    <th>{{ __('Currency') }}</th>
                    <th>{{ __('Default') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($accounts as $account)
                    <tr>
                        <td class="font-semibold">{{ $account['name'] }}</td>
                        <td>{{ $account['bank'] }} · ****{{ $account['last4'] }}</td>
                        <td>{{ $account['currency'] }}</td>
                        <td>
                            @if($account['default'])
                                <x-badge variant="info">{{ __('Default') }}</x-badge>
                            @else
                                <span class="text-slate-500">{{ __('Secondary') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection
