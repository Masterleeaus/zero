@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Token Budgets'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Token Budget Enforcement') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Configure per-user, per-company, and per-intent token spending limits.') }}</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.titan.core.budgets.update') }}">
        @csrf

        {{-- Global Limits --}}
        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-4">{{ __('Global Limits') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    'daily_limit'       => __('Daily Platform Limit (tokens)'),
                    'per_request_max'   => __('Per-Request Max (tokens)'),
                    'per_user_daily'    => __('Per-User Daily Cap (tokens)'),
                    'per_company_daily' => __('Per-Company Daily Cap (tokens)'),
                ] as $field => $label)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                    <input type="number" name="{{ $field }}" min="0"
                        value="{{ $budgetsConfig[$field] ?? 0 }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm bg-background">
                    <p class="text-xs text-muted-foreground mt-1">{{ __('0 = unlimited') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Per-Intent Caps --}}
        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-4">{{ __('Per-Intent Token Caps') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    'text.complete'    => __('text.complete'),
                    'image.generate'   => __('image.generate'),
                    'voice.synthesize' => __('voice.synthesize'),
                    'agent.task'       => __('agent.task'),
                    'code.assist'      => __('code.assist'),
                ] as $intent => $label)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
                    <input type="number" name="intents[{{ $intent }}]" min="0"
                        value="{{ $budgetsConfig['intents'][$intent] ?? 0 }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm bg-background">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Fallback Behaviour --}}
        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-3">{{ __('Budget Exceeded Action') }}</h2>
            <p class="text-sm text-muted-foreground mb-3">{{ __('What should happen when a budget cap is reached?') }}</p>
            <div class="flex gap-4 text-sm">
                @foreach(['deny' => __('Deny request'), 'fallback_model' => __('Fallback to cheaper model'), 'notify_admin' => __('Notify admin only')] as $val => $lbl)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="on_budget_exceeded" value="{{ $val }}"
                        @checked(($budgetsConfig['on_budget_exceeded'] ?? 'deny') === $val)>
                    <span>{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-primary text-primary-foreground px-6 py-2 text-sm font-medium hover:opacity-90">
            {{ __('Save Budget Settings') }}
        </button>
    </form>
</div>
@endsection
