@extends('panel.layout.app')
@section('title', __('Journal Entry') . ': ' . ($entry->reference ?? '#' . $entry->id))
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.journal.index') }}" variant="ghost">
        {{ __('← Back') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-6">
        {{-- Header --}}
        <dl class="grid md:grid-cols-4 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Reference') }}</dt>
                <dd class="mt-1 font-mono">{{ $entry->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Date') }}</dt>
                <dd class="mt-1">{{ $entry->entry_date?->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Currency') }}</dt>
                <dd class="mt-1">{{ $entry->currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    @php
                        $variant = match ($entry->status) {
                            'posted' => 'success',
                            'void'   => 'danger',
                            default  => 'warning',
                        };
                    @endphp
                    <x-badge variant="{{ $variant }}">{{ ucfirst($entry->status) }}</x-badge>
                </dd>
            </div>
            <div class="md:col-span-4">
                <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                <dd class="mt-1">{{ $entry->description }}</dd>
            </div>
            @if($entry->source_type)
                <div class="md:col-span-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Source') }}</dt>
                    <dd class="mt-1 text-sm text-gray-600">{{ class_basename($entry->source_type) }} #{{ $entry->source_id }}</dd>
                </div>
            @endif
        </dl>

        {{-- Lines --}}
        <div>
            <h3 class="text-sm font-semibold mb-2">{{ __('Lines') }}</h3>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Debit') }}</th>
                        <th class="text-end">{{ __('Credit') }}</th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @foreach($entry->lines as $line)
                        <tr>
                            <td>
                                @if($line->account)
                                    <a href="{{ route('dashboard.money.accounts.show', $line->account) }}" class="text-blue-600 hover:underline text-sm">
                                        {{ $line->account->code ? "[{$line->account->code}] " : '' }}{{ $line->account->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">{{ __('Unknown account') }}</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $line->description }}</td>
                            <td class="text-end font-mono">
                                {{ (float)$line->debit > 0 ? number_format((float)$line->debit, 2) : '' }}
                            </td>
                            <td class="text-end font-mono">
                                {{ (float)$line->credit > 0 ? number_format((float)$line->credit, 2) : '' }}
                            </td>
                        </tr>
                    @endforeach
                    {{-- Totals row --}}
                    <tr class="font-semibold border-t">
                        <td colspan="2" class="text-end text-sm text-gray-500">{{ __('Totals') }}</td>
                        <td class="text-end font-mono">{{ number_format($entry->totalDebits(), 2) }}</td>
                        <td class="text-end font-mono">{{ number_format($entry->totalCredits(), 2) }}</td>
                    </tr>
                </x-slot:body>
            </x-table>

            @unless($entry->isBalanced())
                <p class="mt-2 text-red-600 text-sm font-medium">
                    ⚠ {{ __('Warning: this entry is not balanced.') }}
                </p>
            @endunless
        </div>
    </div>
@endsection
