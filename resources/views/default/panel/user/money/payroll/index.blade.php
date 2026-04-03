@extends('panel.layout.app')
@section('title', __('Payroll Runs'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-semibold">{{ __('Payroll Runs') }}</h1>
            <x-button href="{{ route('dashboard.money.payroll.create') }}">{{ __('New Run') }}</x-button>
        </div>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th>{{ __('Pay Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Net Pay') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($payrolls as $payroll)
                    <tr>
                        <td><a href="{{ route('dashboard.money.payroll.show', $payroll) }}" class="text-blue-600 hover:underline">{{ $payroll->reference }}</a></td>
                        <td>{{ $payroll->period_start?->format('Y-m-d') }} – {{ $payroll->period_end?->format('Y-m-d') }}</td>
                        <td>{{ $payroll->pay_date?->format('Y-m-d') }}</td>
                        <td>
                            @php
                                $variant = match($payroll->status) {
                                    'approved' => 'primary',
                                    'paid'     => 'success',
                                    'cancelled'=> 'danger',
                                    default    => 'warning',
                                };
                            @endphp
                            <x-badge variant="{{ $variant }}">{{ ucfirst($payroll->status) }}</x-badge>
                        </td>
                        <td class="text-end">{{ number_format($payroll->total_net, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('dashboard.money.payroll.show', $payroll) }}" class="text-blue-600 hover:underline text-sm">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-4">{{ __('No payroll runs found.') }}</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>

        {{ $payrolls->links() }}
    </div>
@endsection
