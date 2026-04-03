@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Queue Dashboard'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Queue Alignment Dashboard') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Monitor dedicated Titan queues and control job processing.') }}</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Queue Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($stats as $queue => $stat)
        <div class="rounded-xl border p-5 bg-card shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm">{{ $queue }}</h3>
                <span class="px-2 py-0.5 rounded-full text-xs bg-muted text-muted-foreground font-mono">queue</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div class="rounded-lg bg-yellow-50 p-2">
                    <div class="text-xs text-yellow-700 mb-1">{{ __('Pending') }}</div>
                    <div class="text-xl font-bold text-yellow-800">{{ $stat['pending'] }}</div>
                </div>
                <div class="rounded-lg bg-red-50 p-2">
                    <div class="text-xs text-red-700 mb-1">{{ __('Failed') }}</div>
                    <div class="text-xl font-bold text-red-800">{{ $stat['failed'] }}</div>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <form method="POST" action="{{ route('admin.titan.core.queues.retry') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="queue" value="{{ $queue }}">
                    <button type="submit" class="w-full rounded border px-2 py-1 text-xs font-medium hover:bg-green-50 text-green-700 border-green-200">
                        🔄 {{ __('Retry Failed') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.titan.core.queues.flush') }}"
                    onsubmit="return confirm('{{ __('Flush all pending jobs in :queue? This cannot be undone.', ['queue' => $queue]) }}')">
                    @csrf
                    <input type="hidden" name="queue" value="{{ $queue }}">
                    <button type="submit" class="rounded border px-2 py-1 text-xs font-medium hover:bg-red-50 text-red-700 border-red-200">
                        🗑 {{ __('Flush') }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Worker Commands --}}
    <div class="rounded-xl border p-5 bg-card shadow-sm">
        <h2 class="font-semibold mb-4">{{ __('Worker Launch Commands') }}</h2>
        <p class="text-sm text-muted-foreground mb-3">{{ __('Run these commands on your server to start dedicated Titan queue workers:') }}</p>
        <div class="space-y-2">
            @foreach(['titan-ai', 'titan-signals', 'titan-skills'] as $q)
            <div class="flex items-center gap-2">
                <pre class="flex-1 rounded-lg bg-muted px-4 py-2 text-sm font-mono">php artisan queue:work --queue={{ $q }}</pre>
                <button onclick="navigator.clipboard.writeText('php artisan queue:work --queue={{ $q }}')"
                    class="rounded border px-3 py-1 text-xs hover:bg-muted/50">📋 {{ __('Copy') }}</button>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
