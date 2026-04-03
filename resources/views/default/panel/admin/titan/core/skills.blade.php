@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Skill Runtime Monitor'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Skill Runtime Monitor') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Manage and inspect Zylos-managed skill processes.') }}</p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border p-4 bg-card shadow-sm text-center">
            <div class="text-xs text-muted-foreground mb-1">{{ __('Registered') }}</div>
            <div class="text-3xl font-bold">{{ count($skillStatus['registered'] ?? []) }}</div>
        </div>
        <div class="rounded-xl border p-4 bg-green-50 border-green-200 text-center">
            <div class="text-xs text-green-600 mb-1">{{ __('Running') }}</div>
            <div class="text-3xl font-bold text-green-700">{{ count($skillStatus['running'] ?? []) }}</div>
        </div>
        <div class="rounded-xl border p-4 bg-red-50 border-red-200 text-center">
            <div class="text-xs text-red-600 mb-1">{{ __('Failed') }}</div>
            <div class="text-3xl font-bold text-red-700">{{ count($skillStatus['failed'] ?? []) }}</div>
        </div>
    </div>

    {{-- Skill Table --}}
    <div class="rounded-xl border bg-card overflow-x-auto shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/30 text-xs text-muted-foreground">
                    <th class="px-4 py-3 text-left">{{ __('Skill') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Last Event') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Last Heartbeat') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skillStatus['registered'] ?? [] as $skill)
                <tr class="border-b hover:bg-muted/10 transition-colors" x-data="{ showPayload: false }">
                    <td class="px-4 py-3 font-medium">{{ $skill['name'] ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            @if(($skill['last_event'] ?? '') === 'skill.failed') bg-red-100 text-red-700
                            @elseif(($skill['last_event'] ?? '') === 'skill.completed') bg-green-100 text-green-700
                            @elseif(($skill['last_event'] ?? '') === 'skill.started') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $skill['last_event'] ?? 'unknown' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-muted-foreground" title="{{ $skill['last_heartbeat'] ?? '' }}">
                        {{ $skill['last_heartbeat'] ? \Carbon\Carbon::parse($skill['last_heartbeat'])->diffForHumans() : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button
                                hx-post="{{ route('admin.titan.core.skills.restart') }}"
                                hx-vals='{"skill": "{{ $skill['name'] ?? '' }}"}'
                                hx-swap="none"
                                class="rounded border px-3 py-1 text-xs font-medium hover:bg-muted/30">
                                🔄 {{ __('Restart') }}
                            </button>
                            <button
                                hx-post="{{ route('admin.titan.core.skills.disable') }}"
                                hx-vals='{"skill": "{{ $skill['name'] ?? '' }}"}'
                                hx-swap="none"
                                onclick="return confirm('{{ __('Disable this skill?') }}')"
                                class="rounded border px-3 py-1 text-xs font-medium hover:bg-red-50 text-red-600 border-red-200">
                                ⏹ {{ __('Disable') }}
                            </button>
                            @if(!empty($skill['last_payload']))
                            <button @click="showPayload = !showPayload"
                                class="rounded border px-3 py-1 text-xs font-medium hover:bg-muted/30">
                                🔍 {{ __('Payload') }}
                            </button>
                            @endif
                        </div>
                        @if(!empty($skill['last_payload']))
                        <div x-show="showPayload" class="mt-2">
                            <pre class="rounded bg-muted p-2 text-xs font-mono overflow-x-auto">{{ json_encode($skill['last_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">{{ __('No skills registered.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
