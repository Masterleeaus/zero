@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Memory Usage'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Memory Usage Panel') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Overview of AI memory entries, embeddings, and session continuity.') }}</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            'tz_ai_memories'            => __('Memory Entries'),
            'tz_ai_memory_embeddings'   => __('Embeddings'),
            'tz_ai_memory_snapshots'    => __('Snapshots'),
            'tz_ai_session_handoffs'    => __('Session Chains'),
        ] as $table => $label)
        <div class="rounded-xl border p-5 bg-card shadow-sm text-center">
            <div class="text-xs text-muted-foreground mb-2">{{ $label }}</div>
            <div class="text-3xl font-bold">{{ $stats[$table] ?? 'n/a' }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Importance Distribution --}}
        <div class="rounded-xl border p-5 bg-card shadow-sm">
            <h2 class="font-semibold mb-4">{{ __('Importance Score Distribution') }}</h2>
            @if(!empty($importanceDist))
            <div class="space-y-2">
                @foreach($importanceDist as $bucket)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-12 text-right font-mono text-muted-foreground">{{ $bucket['bucket'] }}</span>
                    <div class="flex-1 bg-muted rounded-full h-3">
                        @php $max = collect($importanceDist)->max('cnt'); @endphp
                        <div class="bg-primary rounded-full h-3" style="width:{{ $max > 0 ? round(($bucket['cnt'] / $max) * 100) : 0 }}%"></div>
                    </div>
                    <span class="w-10 text-sm font-medium">{{ $bucket['cnt'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-muted-foreground">{{ __('No importance data available.') }}</p>
            @endif
        </div>

        {{-- TTL Expiry --}}
        <div class="rounded-xl border p-5 bg-card shadow-sm">
            <h2 class="font-semibold mb-4">{{ __('TTL Expiry Schedule') }}</h2>
            <div class="rounded-lg bg-orange-50 border border-orange-200 p-4">
                <div class="text-xs text-orange-600 font-medium mb-1">{{ __('Expiring within 7 days') }}</div>
                <div class="text-3xl font-bold text-orange-700">{{ $expirySoon ?? 0 }}</div>
            </div>
            <p class="text-xs text-muted-foreground mt-3">{{ __('Run a purge to remove all expired entries from the memory store.') }}</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="rounded-xl border p-5 bg-card shadow-sm">
        <h2 class="font-semibold mb-4">{{ __('Memory Actions') }}</h2>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.titan.core.memory.purge') }}">
                @csrf
                <button type="submit"
                    onclick="return confirm('{{ __('Purge all expired memory entries? This cannot be undone.') }}')"
                    class="rounded-lg bg-red-600 text-white px-4 py-2 text-sm font-medium hover:bg-red-700">
                    🗑 {{ __('Purge Expired') }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.titan.core.memory.summarise') }}">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-primary text-primary-foreground px-4 py-2 text-sm font-medium hover:opacity-90">
                    📋 {{ __('Summarise Sessions') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
