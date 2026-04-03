@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Activity Feed'))

@push('css')
<style>
    #activity-feed { height: 500px; overflow-y: auto; }
    .activity-row { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ __('Real-Time Activity Feed') }}</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ __('Live stream of AI requests, memory writes, signal dispatch, and skill execution.') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="ws-status" class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Connecting…') }}</span>
            <button onclick="clearFeed()" class="rounded border px-3 py-1 text-xs hover:bg-muted/30">🗑 {{ __('Clear') }}</button>
        </div>
    </div>

    {{-- Live Feed --}}
    <div class="rounded-xl border bg-card shadow-sm mb-6">
        <div class="border-b px-4 py-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-sm font-medium">{{ __('titan.core.activity') }}</span>
        </div>
        <div id="activity-feed" class="p-4 space-y-1">
            <p class="text-xs text-muted-foreground">{{ __('Waiting for events…') }}</p>
        </div>
    </div>

    {{-- Recent History (server-side) --}}
    <div class="rounded-xl border bg-card shadow-sm overflow-x-auto">
        <div class="border-b px-4 py-3">
            <h2 class="font-semibold text-sm">{{ __('Recent Audit Entries') }}</h2>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b bg-muted/30 text-muted-foreground">
                    <th class="px-4 py-2 text-left">{{ __('Action') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('Process') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('User') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('Time') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('Details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $entry)
                <tr class="border-b hover:bg-muted/10">
                    <td class="px-4 py-2 font-medium">{{ $entry['action'] ?? '—' }}</td>
                    <td class="px-4 py-2 font-mono" title="{{ $entry['process_id'] ?? '' }}">{{ Str::limit($entry['process_id'] ?? '—', 12) }}</td>
                    <td class="px-4 py-2">{{ $entry['performed_by'] ?? '—' }}</td>
                    <td class="px-4 py-2 text-muted-foreground">{{ $entry['created_at'] ?? '—' }}</td>
                    <td class="px-4 py-2 max-w-xs truncate text-muted-foreground">{{ $entry['details'] ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-muted-foreground">{{ __('No recent activity.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js')
<script>
function clearFeed() {
    document.getElementById('activity-feed').innerHTML = '<p class="text-xs text-muted-foreground">Feed cleared.</p>';
}

(function () {
    const feed = document.getElementById('activity-feed');
    const statusEl = document.getElementById('ws-status');

    function appendEvent(data) {
        const row = document.createElement('div');
        row.className = 'activity-row flex gap-3 items-start py-1 border-b border-muted/30 last:border-0';

        const badge = `<span class="shrink-0 px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">${data.event_type || 'event'}</span>`;
        const meta = `<span class="text-xs text-muted-foreground">[${data.timestamp || ''}]</span>`;
        const intent = data.intent ? `<span class="text-xs font-medium">${data.intent}</span>` : '';
        const provider = data.provider ? `<span class="text-xs text-muted-foreground">via ${data.provider}</span>` : '';
        const tokens = data.tokens ? `<span class="text-xs">${data.tokens} tokens</span>` : '';
        const status = `<span class="text-xs ${data.status === 'ok' ? 'text-green-600' : 'text-red-500'}">${data.status || ''}</span>`;

        row.innerHTML = `${badge} ${meta} ${intent} ${provider} ${tokens} ${status}`;

        if (feed.firstChild?.tagName === 'P') feed.innerHTML = '';
        feed.prepend(row);

        // Keep max 200 rows
        while (feed.children.length > 200) feed.removeChild(feed.lastChild);
    }

    @if(config('broadcasting.default') !== 'log' && config('broadcasting.default') !== 'null')
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('titan.core.activity').listen('.titan.activity', function (data) {
            statusEl.textContent = 'Live';
            statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700';
            appendEvent(data);
        });
        statusEl.textContent = 'Connected';
        statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700';
    } else {
        statusEl.textContent = 'Echo not loaded';
        statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700';
    }
    @else
    statusEl.textContent = 'Broadcasting disabled';
    statusEl.className = 'px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500';
    @endif
})();
</script>
@endpush
