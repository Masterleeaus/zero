@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Signal Queue Monitor'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Signal Queue Monitor') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Live view of signals in the TitanCore pipeline.') }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach([
            'pending'  => ['label' => 'Pending',          'color' => 'bg-yellow-50 border-yellow-200 text-yellow-800'],
            'async'    => ['label' => 'Async',             'color' => 'bg-blue-50 border-blue-200 text-blue-800'],
            'awaiting' => ['label' => 'Awaiting Approval', 'color' => 'bg-purple-50 border-purple-200 text-purple-800'],
            'failed'   => ['label' => 'Failed',            'color' => 'bg-red-50 border-red-200 text-red-800'],
            'retry'    => ['label' => 'Retry',             'color' => 'bg-orange-50 border-orange-200 text-orange-800'],
        ] as $key => $cfg)
        <div class="rounded-xl border p-4 {{ $cfg['color'] }}">
            <div class="text-xs font-medium mb-1">{{ $cfg['label'] }}</div>
            <div class="text-2xl font-bold">{{ $stats[$key] ?? 0 }}</div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="company_id" value="{{ request('company_id') }}"
            placeholder="{{ __('Company ID') }}"
            class="rounded-lg border px-3 py-2 text-sm bg-background w-32">
        <input type="text" name="signal_type" value="{{ request('signal_type') }}"
            placeholder="{{ __('Signal Type') }}"
            class="rounded-lg border px-3 py-2 text-sm bg-background w-40">
        <select name="status" class="rounded-lg border px-3 py-2 text-sm bg-background">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach(['pending','async','awaiting_approval','failed','dispatched'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <select name="age" class="rounded-lg border px-3 py-2 text-sm bg-background">
            <option value="">{{ __('Any Age') }}</option>
            @foreach([1 => '1h', 6 => '6h', 24 => '24h', 48 => '48h', 168 => '7d'] as $h => $label)
            <option value="{{ $h }}" @selected(request('age') == $h)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-primary text-primary-foreground px-4 py-2 text-sm font-medium">{{ __('Filter') }}</button>
        <a href="{{ route('admin.titan.core.signals') }}" class="rounded-lg border px-4 py-2 text-sm font-medium">{{ __('Reset') }}</a>
    </form>

    {{-- Table --}}
    <div class="rounded-xl border bg-card overflow-x-auto shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/30 text-xs text-muted-foreground">
                    <th class="px-4 py-3 text-left">{{ __('Signal ID') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Type') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Company') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Retries') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Broadcast') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signals as $signal)
                <tr class="border-b hover:bg-muted/10 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs">{{ $signal->signal_id ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $signal->signal_type ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            @if(($signal->broadcast_status ?? '') === 'failed') bg-red-100 text-red-700
                            @elseif(($signal->broadcast_status ?? '') === 'dispatched') bg-green-100 text-green-700
                            @elseif(($signal->broadcast_status ?? '') === 'pending') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $signal->broadcast_status ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $signal->company_id ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $signal->retry_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ $signal->created_at ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ $signal->broadcast_at ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">{{ __('No signals found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $signals->withQueryString()->links() }}</div>
</div>
@endsection
