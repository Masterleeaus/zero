@extends('default.layout.app')
@section('content')
    <div class="max-w-6xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Credit Notes') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Money Back & Adjustments') }}</h1>
            </div>
            <x-button href="{{ route('dashboard.money.credit-notes.create') }}">
                <x-tabler-plus class="size-4" />
                {{ __('New Credit Note') }}
            </x-button>
        </div>

        <form class="flex gap-3 items-center">
            <x-select name="status" label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach(['draft','issued','applied','void'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-select>
            <x-button type="submit" variant="secondary">{{ __('Filter') }}</x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Number') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($creditNotes as $note)
                    @if(empty($filters['status']) || $filters['status'] === $note['status'])
                        <tr>
                            <td class="font-semibold">{{ $note['number'] }}</td>
                            <td>{{ $note['customer'] }}</td>
                            <td><x-badge variant="info">{{ ucfirst($note['status']) }}</x-badge></td>
                            <td class="text-end">${{ number_format($note['total'], 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection

