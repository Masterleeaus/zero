@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – System Health'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ __('System Health Dashboard') }}</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ __('MCP, router, memory, signal, and skill health status checks.') }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.location.reload()" class="rounded-lg border px-4 py-2 text-sm hover:bg-muted/30">
                🔄 {{ __('Refresh') }}
            </button>
            <a href="{{ route('admin.titan.core.health.api') }}" target="_blank"
                class="rounded-lg border px-4 py-2 text-sm hover:bg-muted/30">
                📡 {{ __('JSON') }}
            </a>
        </div>
    </div>

    {{-- Overall Status --}}
    @php
        $allPass = collect($checks)->every(fn($c) => $c['pass'] ?? false);
        $failCount = collect($checks)->filter(fn($c) => !($c['pass'] ?? false))->count();
    @endphp
    <div class="rounded-xl border p-4 mb-6 {{ $allPass ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
        <div class="flex items-center gap-3">
            <span class="text-2xl">{{ $allPass ? '✅' : '⚠️' }}</span>
            <div>
                <div class="font-semibold">{{ $allPass ? __('All systems operational') : __(':count check(s) failed', ['count' => $failCount]) }}</div>
                <div class="text-xs text-muted-foreground">{{ now()->toDateTimeString() }}</div>
            </div>
        </div>
    </div>

    {{-- Check Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($checks as $name => $check)
        <div class="rounded-xl border p-5 bg-card shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="font-medium text-sm">{{ str_replace('_', ' ', ucfirst($name)) }}</span>
                <span class="text-lg">{{ ($check['pass'] ?? false) ? '✅' : '❌' }}</span>
            </div>
            <div class="text-xs text-muted-foreground {{ ($check['pass'] ?? false) ? 'text-green-700' : 'text-red-600' }}">
                {{ $check['detail'] ?? '—' }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- MCP Info --}}
    <div class="rounded-xl border p-5 bg-card shadow-sm mt-6">
        <h2 class="font-semibold mb-3">{{ __('MCP Server Configuration') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs text-muted-foreground mb-1">{{ __('HTTP Transport URL') }}</div>
                <div class="font-mono">{{ config('titan_core.mcp.http_url', env('MCP_HTTP_URL', __('Not configured'))) }}</div>
            </div>
            <div>
                <div class="text-xs text-muted-foreground mb-1">{{ __('Rate Limit') }}</div>
                <div>{{ config('titan_core.mcp.rate_limit', 100) }} {{ __('req/min') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
