@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('PWA Diagnostics'))
@section('titlebar_subtitle', __('Registered devices, trust levels, and signal ingress health'))

@section('content')
<div class="py-10">

    {{-- Summary Cards --}}
    <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Devices') }}</div>
            <div class="mt-1 text-2xl font-bold text-white">{{ $stats['summary']['total_devices'] }}</div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Rate Limited') }}</div>
            <div class="mt-1 text-2xl font-bold {{ $stats['summary']['rate_limited_devices'] > 0 ? 'text-red-400' : 'text-green-400' }}">
                {{ $stats['summary']['rate_limited_devices'] }}
            </div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Suspicious') }}</div>
            <div class="mt-1 text-2xl font-bold {{ $stats['summary']['suspicious_nodes'] > 0 ? 'text-amber-400' : 'text-green-400' }}">
                {{ $stats['summary']['suspicious_nodes'] }}
            </div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Total Ingress') }}</div>
            <div class="mt-1 text-2xl font-bold text-white">{{ $stats['summary']['total_ingress'] }}</div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Pending') }}</div>
            <div class="mt-1 text-2xl font-bold {{ $stats['summary']['pending_ingress'] > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                {{ $stats['summary']['pending_ingress'] }}
            </div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ __('Failed') }}</div>
            <div class="mt-1 text-2xl font-bold {{ $stats['summary']['failed_ingress'] > 0 ? 'text-red-400' : 'text-green-400' }}">
                {{ $stats['summary']['failed_ingress'] }}
            </div>
        </div>
    </div>

    {{-- Trust Breakdown + Promotion Stats --}}
    <div class="mb-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Trust Breakdown --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Trust Level Breakdown') }}</h3>
            @php $levels = ['untrusted' => 'red', 'provisional' => 'amber', 'trusted' => 'blue', 'verified' => 'green']; @endphp
            @foreach($levels as $level => $color)
                @php $count = $stats['summary']['trust_breakdown'][$level] ?? 0; @endphp
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs text-slate-400 capitalize">{{ $level }}</span>
                    <span class="rounded-full bg-{{ $color }}-500/20 px-2 py-0.5 text-xs font-bold text-{{ $color }}-400">{{ $count }}</span>
                </div>
            @endforeach
        </div>

        {{-- Ingress Health --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Signal Ingress Health') }}</h3>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Promoted') }}</span>
                <span class="rounded-full bg-green-500/20 px-2 py-0.5 text-xs font-bold text-green-400">{{ $stats['summary']['promoted_ingress'] }}</span>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Pending') }}</span>
                <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-bold text-amber-400">{{ $stats['summary']['pending_ingress'] }}</span>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Failed') }}</span>
                <span class="rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-bold text-red-400">{{ $stats['summary']['failed_ingress'] }}</span>
            </div>
            @if($stats['summary']['last_promotion_at'])
            <div class="mt-3 text-xs text-slate-500">
                {{ __('Last promotion:') }} {{ $stats['summary']['last_promotion_at'] }}
            </div>
            @endif
        </div>
    </div>

    {{-- Device Table --}}
    <div class="rounded-xl border border-white/10 bg-white/5">
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-300">{{ __('Registered Devices') }}</h3>
            <span class="text-xs text-slate-500">{{ __('Most recently seen first') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-xs text-slate-500">
                        <th class="px-4 py-3 text-left">{{ __('Node') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Platform') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Version') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Trust') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Failures') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Queue') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Last Seen') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['devices'] as $device)
                    <tr class="border-b border-white/5 hover:bg-white/3" x-data="{}">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs text-slate-300">{{ substr($device['node_id'], 0, 12) }}…</div>
                            @if($device['device_label'])
                            <div class="text-xs text-slate-500 truncate max-w-[140px]" title="{{ $device['device_label'] }}">{{ Str::limit($device['device_label'], 30) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $device['platform'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $device['app_version'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $trustColors = ['untrusted' => 'red', 'provisional' => 'amber', 'trusted' => 'blue', 'verified' => 'green'];
                                $tc = $trustColors[$device['trust_level']] ?? 'slate';
                            @endphp
                            <span class="rounded-full bg-{{ $tc }}-500/20 px-2 py-0.5 text-xs font-semibold text-{{ $tc }}-400">
                                {{ $device['trust_level'] }}
                            </span>
                            @if($device['is_rate_limited'])
                            <span class="ml-1 rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-semibold text-red-400">rate-limited</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs {{ $device['signature_failures'] >= 3 ? 'text-red-400 font-bold' : 'text-slate-400' }}">
                            {{ $device['signature_failures'] }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $device['pending_count'] }} / {{ $device['signal_count'] }}
                            @if($device['failed_count'] > 0)
                                <span class="text-red-400">({{ $device['failed_count'] }} failed)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $device['last_seen_at'] ? \Carbon\Carbon::parse($device['last_seen_at'])->diffForHumans() : __('Never') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                @if($device['is_rate_limited'])
                                <button
                                    class="rounded bg-amber-500/20 px-2 py-1 text-xs text-amber-400 hover:bg-amber-500/30"
                                    onclick="pwaOp('clear-rate-limit', '{{ $device['node_id'] }}')"
                                >{{ __('Unblock') }}</button>
                                @endif
                                @if(in_array($device['trust_level'], ['provisional', 'untrusted']))
                                <button
                                    class="rounded bg-blue-500/20 px-2 py-1 text-xs text-blue-400 hover:bg-blue-500/30"
                                    onclick="pwaOp('promote', '{{ $device['node_id'] }}', 'trusted')"
                                >{{ __('Trust') }}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-xs text-slate-500">{{ __('No devices registered yet.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
async function pwaOp(action, nodeId, trustLevel) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const endpoints = {
        'promote': '/pwa/diagnostics/nodes/promote',
        'clear-rate-limit': '/pwa/diagnostics/nodes/clear-rate-limit',
    };

    const body = action === 'promote'
        ? { node_id: nodeId, trust_level: trustLevel }
        : { node_id: nodeId };

    try {
        const res = await fetch(endpoints[action], {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.ok || data.trust_level) {
            window.location.reload();
        } else {
            alert(data.reason ?? 'Operation failed');
        }
    } catch (e) {
        alert('Request failed: ' + e.message);
    }
}
</script>
@endpush
