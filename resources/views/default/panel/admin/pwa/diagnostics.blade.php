@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('PWA Diagnostics'))
@section('titlebar_subtitle', __('Devices, trust, signals, conflicts, and queue health'))

@section('content')
<div class="py-10 space-y-8">

    {{-- ── Summary Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
        @php
        $cards = [
            ['label' => __('Devices'), 'value' => $stats['summary']['total_devices'], 'color' => 'slate'],
            ['label' => __('Rate Limited'), 'value' => $stats['summary']['rate_limited_devices'], 'color' => $stats['summary']['rate_limited_devices'] > 0 ? 'red' : 'green'],
            ['label' => __('Suspicious'), 'value' => $stats['summary']['suspicious_nodes'], 'color' => $stats['summary']['suspicious_nodes'] > 0 ? 'amber' : 'green'],
            ['label' => __('Stale Nodes'), 'value' => $stats['summary']['stuck_offline_nodes'], 'color' => $stats['summary']['stuck_offline_nodes'] > 0 ? 'amber' : 'green'],
            ['label' => __('Conflicts'), 'value' => $stats['summary']['unresolved_conflicts'], 'color' => $stats['summary']['unresolved_conflicts'] > 0 ? 'red' : 'green'],
            ['label' => __('Dead Letters'), 'value' => $stats['summary']['dead_letter_count'], 'color' => $stats['summary']['dead_letter_count'] > 0 ? 'red' : 'green'],
            ['label' => __('Pending'), 'value' => $stats['summary']['pending_ingress'], 'color' => $stats['summary']['pending_ingress'] > 0 ? 'amber' : 'slate'],
            ['label' => __('Failed'), 'value' => $stats['summary']['failed_ingress'], 'color' => $stats['summary']['failed_ingress'] > 0 ? 'red' : 'green'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <div class="text-xs text-slate-400">{{ $card['label'] }}</div>
            <div class="mt-1 text-2xl font-bold text-{{ $card['color'] }}-400">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Row 1: Trust + Ingress Health + Tier Breakdown ────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

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
            @php
            $stages = [
                ['label' => __('Promoted'), 'key' => 'promoted_ingress', 'color' => 'green'],
                ['label' => __('Pending'), 'key' => 'pending_ingress', 'color' => 'amber'],
                ['label' => __('Deferred'), 'key' => 'deferred_ingress', 'color' => 'blue'],
                ['label' => __('Failed'), 'key' => 'failed_ingress', 'color' => 'red'],
            ];
            @endphp
            @foreach($stages as $s)
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ $s['label'] }}</span>
                <span class="rounded-full bg-{{ $s['color'] }}-500/20 px-2 py-0.5 text-xs font-bold text-{{ $s['color'] }}-400">
                    {{ $stats['summary'][$s['key']] ?? 0 }}
                </span>
            </div>
            @endforeach
            @if($stats['summary']['last_promotion_at'])
            <div class="mt-3 text-xs text-slate-500">{{ __('Last promotion:') }} {{ $stats['summary']['last_promotion_at'] }}</div>
            @endif
            <div class="mt-3">
                <button onclick="triggerReplay()"
                    class="rounded bg-indigo-500/20 px-3 py-1 text-xs text-indigo-300 hover:bg-indigo-500/30">
                    {{ __('Trigger Replay') }}
                </button>
            </div>
        </div>

        {{-- Capability Tier Breakdown --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Device Capability Tiers') }}</h3>
            @php
            $tiers = [
                'desktop_full'     => ['label' => 'Desktop Full', 'color' => 'green'],
                'tablet_standard'  => ['label' => 'Tablet Standard', 'color' => 'blue'],
                'mobile_standard'  => ['label' => 'Mobile Standard', 'color' => 'amber'],
                'mobile_light'     => ['label' => 'Mobile Light', 'color' => 'slate'],
            ];
            @endphp
            @foreach($tiers as $tier => $t)
                @php $count = $stats['summary']['tier_breakdown'][$tier] ?? 0; @endphp
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ $t['label'] }}</span>
                    <span class="rounded-full bg-{{ $t['color'] }}-500/20 px-2 py-0.5 text-xs font-bold text-{{ $t['color'] }}-400">{{ $count }}</span>
                </div>
            @endforeach
            @php $unknown = $stats['summary']['tier_breakdown'][null] ?? $stats['summary']['tier_breakdown'][''] ?? 0; @endphp
            @if($unknown)
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Unknown') }}</span>
                <span class="rounded-full bg-slate-500/20 px-2 py-0.5 text-xs font-bold text-slate-400">{{ $unknown }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Queue Health (from PwaQueueHealthService) ───────────────────────────── --}}
    @if(isset($queueHealth))
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Dead Letters + Deferred Ready --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Queue Health') }}</h3>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Deferred ready to replay') }}</span>
                <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-bold text-amber-400">
                    {{ $queueHealth['ingress']['deferred_ready'] ?? 0 }}
                </span>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Dead letters') }}</span>
                <span class="rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-bold text-red-400">
                    {{ $queueHealth['ingress']['dead_letter_count'] ?? 0 }}
                </span>
            </div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Avg retries (failed)') }}</span>
                <span class="text-xs text-slate-300">{{ $queueHealth['ingress']['avg_retries'] ?? '0' }}</span>
            </div>
            @if($queueHealth['ingress']['oldest_pending'])
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ __('Oldest pending') }}</span>
                <span class="text-xs text-slate-300">{{ \Carbon\Carbon::parse($queueHealth['ingress']['oldest_pending'])->diffForHumans() }}</span>
            </div>
            @endif
        </div>

        {{-- Conflict Summary --}}
        <div class="rounded-xl border border-white/10 bg-white/5 p-5">
            <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Conflict Events (24h)') }}</h3>
            @php
            $conflictColors = [
                'invalid_signature' => 'red',
                'consensus_fail'    => 'amber',
                'timestamp_drift'   => 'yellow',
                'duplicate'         => 'slate',
                'trust_override'    => 'orange',
            ];
            @endphp
            @forelse($queueHealth['conflicts']['by_type'] ?? [] as $type => $count)
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-mono">{{ $type }}</span>
                    <span class="rounded-full bg-{{ $conflictColors[$type] ?? 'slate' }}-500/20 px-2 py-0.5 text-xs font-bold text-{{ $conflictColors[$type] ?? 'slate' }}-400">
                        {{ $count }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-slate-500">{{ __('No conflict events recorded.') }}</p>
            @endforelse
            @if(($queueHealth['conflicts']['unresolved_count'] ?? 0) > 0)
            <div class="mt-3 text-xs text-amber-400">
                ⚠ {{ $queueHealth['conflicts']['unresolved_count'] }} {{ __('unresolved conflicts') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Staging Health --}}
    @if(($queueHealth['staging']['total'] ?? 0) > 0)
    <div class="rounded-xl border border-white/10 bg-white/5 p-5">
        <h3 class="mb-4 text-sm font-semibold text-slate-300">{{ __('Staged Offline Artifacts') }}</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach($queueHealth['staging']['by_stage'] ?? [] as $stage => $count)
            <div>
                <div class="text-xs text-slate-500 capitalize">{{ $stage }}</div>
                <div class="text-lg font-bold text-white">{{ $count }}</div>
            </div>
            @endforeach
        </div>
        @if(!empty($queueHealth['staging']['by_type']))
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($queueHealth['staging']['by_type'] as $type => $count)
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs text-slate-300">{{ $type }}: {{ $count }}</span>
            @endforeach
        </div>
        @endif
    </div>
    @endif
    @endif

    {{-- ── Device Table ────────────────────────────────────────────────────────── --}}
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
                        <th class="px-4 py-3 text-left">{{ __('Platform / Tier') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Version') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Trust') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Failures') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Queue') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Last Seen / Success') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['devices'] as $device)
                    <tr class="border-b border-white/5 hover:bg-white/3">
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs text-slate-300">{{ substr($device['node_id'], 0, 12) }}…</div>
                            @if($device['device_label'])
                            <div class="text-xs text-slate-500 truncate max-w-[140px]" title="{{ $device['device_label'] }}">
                                {{ Str::limit($device['device_label'], 30) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-slate-400">{{ $device['platform'] ?? '—' }}</div>
                            @if($device['capability_tier'])
                            <div class="text-xs text-indigo-400/80">{{ $device['capability_tier'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-slate-400">{{ $device['app_version'] ?? '—' }}</div>
                            @if($device['runtime_version'])
                            <div class="text-xs text-slate-500">rt:{{ $device['runtime_version'] }}</div>
                            @endif
                        </td>
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
                            @if($device['queue_backlog'] > 0)
                            <span class="text-amber-400 font-semibold">{{ $device['queue_backlog'] }} backlog</span>
                            @endif
                            {{ $device['pending_count'] }}/{{ $device['signal_count'] }}
                            @if($device['failed_count'] > 0)
                                <span class="text-red-400"> ({{ $device['failed_count'] }} ✕)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            <div>{{ $device['last_seen_at'] ? \Carbon\Carbon::parse($device['last_seen_at'])->diffForHumans() : __('Never') }}</div>
                            @if($device['last_success_at'])
                            <div class="text-green-400/70">✓ {{ \Carbon\Carbon::parse($device['last_success_at'])->diffForHumans() }}</div>
                            @endif
                            @if($device['last_failure_at'])
                            <div class="text-red-400/70">✕ {{ \Carbon\Carbon::parse($device['last_failure_at'])->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @if($device['is_rate_limited'])
                                <button class="rounded bg-amber-500/20 px-2 py-1 text-xs text-amber-400 hover:bg-amber-500/30"
                                    onclick="pwaOp('clear-rate-limit', '{{ $device['node_id'] }}')">{{ __('Unblock') }}</button>
                                @endif
                                @if(in_array($device['trust_level'], ['provisional', 'untrusted']))
                                <button class="rounded bg-blue-500/20 px-2 py-1 text-xs text-blue-400 hover:bg-blue-500/30"
                                    onclick="pwaOp('promote', '{{ $device['node_id'] }}', 'trusted')">{{ __('Trust') }}</button>
                                @endif
                                <button class="rounded bg-indigo-500/20 px-2 py-1 text-xs text-indigo-400 hover:bg-indigo-500/30"
                                    onclick="triggerReplay('{{ $device['node_id'] }}')">{{ __('Replay') }}</button>
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

    {{-- ── Recent Conflicts Table ──────────────────────────────────────────────── --}}
    @if(isset($queueHealth) && count($queueHealth['conflicts']['recent_24h'] ?? []) > 0)
    <div class="rounded-xl border border-white/10 bg-white/5">
        <div class="border-b border-white/10 px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-300">{{ __('Recent Conflict Events (24h)') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-xs text-slate-500">
                        <th class="px-4 py-3 text-left">{{ __('Node') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Signal') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Conflict') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Reason') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('When') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($queueHealth['conflicts']['recent_24h'] as $c)
                    <tr class="border-b border-white/5 hover:bg-white/3">
                        <td class="px-4 py-2 font-mono text-xs text-slate-400">{{ substr($c['node_id'], 0, 12) }}…</td>
                        <td class="px-4 py-2 text-xs text-slate-400">{{ $c['signal_key'] }}</td>
                        <td class="px-4 py-2">
                            @php
                            $ct = $c['conflict_type'];
                            $ctColor = match($ct) {
                                'invalid_signature' => 'red',
                                'consensus_fail'    => 'amber',
                                default             => 'slate',
                            };
                            @endphp
                            <span class="rounded-full bg-{{ $ctColor }}-500/20 px-2 py-0.5 text-xs font-semibold text-{{ $ctColor }}-400">{{ $ct }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-400">{{ $c['status'] }}</td>
                        <td class="px-4 py-2 text-xs text-slate-500 max-w-xs truncate" title="{{ $c['reason'] ?? '' }}">
                            {{ Str::limit($c['reason'] ?? '—', 60) }}
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-400">
                            {{ $c['at'] ? \Carbon\Carbon::parse($c['at'])->diffForHumans() : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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

async function triggerReplay(nodeId = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const body = nodeId ? { node_id: nodeId } : {};
        const res = await fetch('/pwa/diagnostics/replay', {
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
        if (data.ok) {
            alert(`Replay triggered: ${data.replayed} replayed, ${data.skipped ?? 0} skipped`);
            window.location.reload();
        } else {
            alert('Replay failed');
        }
    } catch (e) {
        alert('Request failed: ' + e.message);
    }
}
</script>
@endpush


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
