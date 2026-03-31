@extends('default.layout.app')
@section('content')
    <div class="max-w-6xl mx-auto py-10 space-y-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ workcore_label('customer') }}</p>
                <h1 class="text-2xl font-semibold mt-1">{{ $customer->name }}</h1>
                <p class="text-slate-500 mt-1">{{ __('Status') }}: <span class="font-medium text-slate-700">{{ ucfirst($customer->status) }}</span></p>
            </div>
            <div class="flex gap-3">
                <x-button href="{{ route('dashboard.crm.customers.edit', $customer) }}">
                    <x-tabler-pencil class="size-4" />
                    {{ __('Edit') }}
                </x-button>
            </div>
        </div>

        <x-card>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Email') }}</p>
                    <p class="font-semibold">{{ $customer->email ?: __('Not provided') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Phone') }}</p>
                    <p class="font-semibold">{{ $customer->phone ?: __('Not provided') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Notes') }}</p>
                    <p class="font-semibold whitespace-pre-line">{{ $customer->notes ?: __('None') }}</p>
                </div>
            </div>
        </x-card>

        <x-card x-data="{ tab: 'contacts' }" class="p-0">
            <div class="border-b px-4 py-3 flex gap-3 flex-wrap">
                @foreach(['contacts','notes','documents','deals','quotes','invoices'] as $key)
                    <button type="button"
                            class="px-3 py-2 rounded-md text-sm font-semibold"
                            :class="tab === '{{ $key }}' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                            x-on:click="tab='{{ $key }}'">
                        {{ __('crm.customers.' . $key) }}
                    </button>
                @endforeach
            </div>
            <div class="p-4 space-y-4">
                <div x-show="tab === 'contacts'" class="space-y-3" x-cloak>
                    @foreach($contacts as $contact)
                        <div class="border rounded-lg px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $contact['name'] }}</div>
                                <div class="text-sm text-slate-500">{{ $contact['role'] }}</div>
                                <div class="text-sm text-slate-500">{{ $contact['email'] }} · {{ $contact['phone'] }}</div>
                            </div>
                            <x-badge variant="info">{{ __('Primary') }}</x-badge>
                        </div>
                    @endforeach
                </div>

                <div x-show="tab === 'notes'" class="grid md:grid-cols-2 gap-3" x-cloak>
                    @foreach($notes as $note)
                        <div class="border rounded-lg px-4 py-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold">{{ $note['title'] }}</div>
                                @if($note['pinned'])
                                    <x-tabler-pin class="size-4 text-amber-500" />
                                @endif
                            </div>
                            <p class="text-slate-600 text-sm">{{ $note['body'] }}</p>
                            <p class="text-xs text-slate-500">{{ __('By :author on :date', ['author' => $note['author'], 'date' => $note['created_at']]) }}</p>
                        </div>
                    @endforeach
                </div>

                <div x-show="tab === 'documents'" class="space-y-2" x-cloak>
                    @foreach($documents as $document)
                        <div class="border rounded-lg px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $document['name'] }}</div>
                                <div class="text-sm text-slate-500">{{ $document['size'] }} · {{ __('Uploaded by :user', ['user' => $document['uploaded_by']]) }}</div>
                            </div>
                            <x-button variant="ghost">
                                <x-tabler-download class="size-4" />
                                {{ __('Download') }}
                            </x-button>
                        </div>
                    @endforeach
                </div>

                <div x-show="tab === 'deals'" class="space-y-2" x-cloak>
                    @foreach($deals as $deal)
                        <div class="border rounded-lg px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $deal['title'] }}</div>
                                <div class="text-sm text-slate-500">{{ __('Value: :value', ['value' => '$' . number_format($deal['value'], 2)]) }}</div>
                            </div>
                            <x-badge variant="info">{{ ucfirst($deal['stage']) }}</x-badge>
                        </div>
                    @endforeach
                </div>

                <div x-show="tab === 'quotes'" class="space-y-2" x-cloak>
                    @forelse($quotes as $quote)
                        <div class="border rounded-lg px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $quote->quote_number }}</div>
                                <div class="text-sm text-slate-500">{{ $quote->title }}</div>
                            </div>
                            <x-button variant="ghost" href="{{ route('dashboard.money.quotes.show', $quote) }}">
                                <x-tabler-eye class="size-4" />
                                {{ __('Open') }}
                            </x-button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">{{ __('No quotes yet.') }}</p>
                    @endforelse
                </div>

                <div x-show="tab === 'invoices'" class="space-y-2" x-cloak>
                    @forelse($invoices as $invoice)
                        <div class="border rounded-lg px-4 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $invoice->invoice_number }}</div>
                                <div class="text-sm text-slate-500">{{ $invoice->title }}</div>
                            </div>
                            <x-button variant="ghost" href="{{ route('dashboard.money.invoices.show', $invoice) }}">
                                <x-tabler-eye class="size-4" />
                                {{ __('Open') }}
                            </x-button>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">{{ __('No invoices yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>
@endsection
