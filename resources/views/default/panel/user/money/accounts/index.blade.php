@extends('panel.layout.app')
@section('title', __('Chart of Accounts'))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.journal.index') }}" variant="ghost">
        <x-tabler-book class="size-4" />
        {{ __('Journal Entries') }}
    </x-button>
@endsection

@section('content')
<div class="py-6">
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Create Account Form --}}
        <div class="lg:col-span-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 h-fit shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-5">{{ __('Create New Account') }}</h3>
            <form action="{{ route('dashboard.money.accounts.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Account Name') }}</label>
                    <x-input type="text" name="name" required placeholder="{{ __('Checking, Office Supplies…') }}" />
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Code') }}</label>
                        <x-input type="text" name="code" placeholder="1000" />
                        @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('GL Type') }}</label>
                        <x-select name="gl_type" required>
                            <option value="asset">{{ __('Asset (1xxx)') }}</option>
                            <option value="liability">{{ __('Liability (2xxx)') }}</option>
                            <option value="equity">{{ __('Equity (3xxx)') }}</option>
                            <option value="revenue">{{ __('Revenue (4xxx)') }}</option>
                            <option value="expense">{{ __('Expense (5xxx)') }}</option>
                        </x-select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Detail Type') }}</label>
                    <x-select name="type" required>
                        <option value="bank">{{ __('Bank') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="current_asset">{{ __('Current Asset') }}</option>
                        <option value="fixed_asset">{{ __('Fixed Asset') }}</option>
                        <option value="accounts_receivable">{{ __('Accounts Receivable') }}</option>
                        <option value="credit_card">{{ __('Credit Card') }}</option>
                        <option value="current_liability">{{ __('Current Liability') }}</option>
                        <option value="accounts_payable">{{ __('Accounts Payable') }}</option>
                        <option value="long_term_liability">{{ __('Long Term Liability') }}</option>
                        <option value="equity">{{ __('Equity') }}</option>
                        <option value="income">{{ __('Income') }}</option>
                        <option value="cogs">{{ __('Cost of Goods Sold') }}</option>
                        <option value="expense">{{ __('Expense') }}</option>
                        <option value="wages">{{ __('Wages &amp; Salaries') }}</option>
                    </x-select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Parent Account') }} <span class="text-slate-400 normal-case font-normal">({{ __('optional') }})</span></label>
                    <x-select name="parent_id">
                        <option value="">{{ __('None (Top Level)') }}</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code ? $acc->code . ' – ' : '' }}{{ $acc->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Opening Balance') }}</label>
                    <x-input type="number" step="0.01" name="balance" value="0.00" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Bank Name') }}</label>
                        <x-input type="text" name="bank_name" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1.5 font-semibold uppercase tracking-wider">{{ __('Account #') }}</label>
                        <x-input type="text" name="account_number" />
                    </div>
                </div>
                <x-button type="submit" class="w-full">{{ __('Create Account') }}</x-button>
            </form>
        </div>

        {{-- Accounts List --}}
        <div class="lg:col-span-3 space-y-6">
            @php
                $glTypes = [
                    'asset'     => ['title' => __('Assets'),      'color' => 'text-emerald-500', 'dot' => 'bg-emerald-500'],
                    'liability' => ['title' => __('Liabilities'),  'color' => 'text-rose-500',    'dot' => 'bg-rose-500'],
                    'equity'    => ['title' => __('Equity'),       'color' => 'text-indigo-500',  'dot' => 'bg-indigo-500'],
                    'revenue'   => ['title' => __('Revenue'),      'color' => 'text-blue-500',    'dot' => 'bg-blue-500'],
                    'expense'   => ['title' => __('Expenses'),     'color' => 'text-amber-500',   'dot' => 'bg-amber-500'],
                ];
            @endphp

            @forelse($glTypes as $key => $config)
                @if(isset($groupedAccounts[$key]) && $groupedAccounts[$key]->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $config['dot'] }}"></span>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm {{ $config['color'] }}">{{ $config['title'] }}</h3>
                        <span class="ml-auto text-xs text-slate-500">{{ $groupedAccounts[$key]->count() }} {{ __('accounts') }}</span>
                    </div>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/30 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">{{ __('Code') }}</th>
                                <th class="px-4 py-3">{{ __('Account Name') }}</th>
                                <th class="px-4 py-3">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @foreach($groupedAccounts[$key] as $acc)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs {{ $acc->code ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400' }}">
                                    {{ $acc->code ?? '—' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    {{ $acc->name }}
                                    @if($acc->bank_name || $acc->account_number)
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $acc->bank_name }}{{ $acc->account_number ? ' · ' . $acc->account_number : '' }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase tracking-wide">
                                        {{ str_replace('_', ' ', $acc->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold {{ $acc->balance < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                    ${{ number_format(abs($acc->balance), 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            @empty
            @endforelse

            @if($groupedAccounts->isEmpty())
                <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-600">
                    <x-tabler-building-bank class="size-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ __('No Accounts Yet') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Create your first account using the form on the left.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
