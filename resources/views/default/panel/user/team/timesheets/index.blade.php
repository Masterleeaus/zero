@extends('default.layout.app')
@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 uppercase tracking-wide">{{ __('Timesheets') }}</p>
                <h1 class="text-2xl font-semibold">{{ __('Weekly submissions') }}</h1>
            </div>
        </div>

        <form class="flex gap-3 items-end">
            <x-select name="status" label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach(['pending','submitted','approved'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-select>
            <x-button type="submit" variant="secondary">{{ __('Filter') }}</x-button>
        </form>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>{{ __('Number') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Hours') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @foreach($timesheets as $sheet)
                    <tr>
                        <td class="font-semibold">{{ $sheet['number'] }}</td>
                        <td>{{ $sheet['period'] }}</td>
                        <td><x-badge variant="info">{{ ucfirst($sheet['status']) }}</x-badge></td>
                        <td>{{ $sheet['hours'] }}</td>
                        <td class="text-end">${{ number_format($sheet['total'], 2) }}</td>
                        <td class="text-end space-x-1">
                            <x-button variant="ghost" size="none" class="size-8" href="{{ route('dashboard.team.timesheets.show', $sheet['number']) }}">
                                <x-tabler-eye class="size-4" />
                            </x-button>
                            <x-button variant="ghost" size="none" class="size-8">
                                <x-tabler-check class="size-4 text-emerald-600" />
                            </x-button>
                            <x-button variant="ghost" size="none" class="size-8">
                                <x-tabler-x class="size-4 text-rose-600" />
                            </x-button>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection

