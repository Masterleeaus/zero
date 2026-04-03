@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Titan Core – Model Routing'))

@section('content')
<div class="py-10 container-xl max-w-6xl mx-auto px-4">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('Active Model Routing') }}</h1>
        <p class="text-sm text-muted-foreground mt-1">{{ __('Configure default models and per-intent overrides for TitanAIRouter.') }}</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Router Status --}}
    <div class="mb-6 rounded-xl border p-5 bg-card shadow-sm">
        <h2 class="font-semibold mb-3">{{ __('Router Status') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @foreach($routerStatus as $key => $val)
            <div class="rounded-lg bg-muted/30 p-3">
                <div class="text-xs text-muted-foreground mb-1">{{ $key }}</div>
                <div class="font-medium">{{ is_bool($val) ? ($val ? '✅ Yes' : '❌ No') : $val }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Model Config Form --}}
    <form method="POST" action="{{ route('admin.titan.core.models.update') }}">
        @csrf
        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-4">{{ __('Default Models') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Default Text Model') }}</label>
                    <input type="text" name="default_text_model"
                        value="{{ $aiConfig['default_text_model'] ?? '' }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm bg-background"
                        placeholder="gpt-4o">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('Default Image Model') }}</label>
                    <input type="text" name="default_image_model"
                        value="{{ $aiConfig['default_image_model'] ?? '' }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm bg-background"
                        placeholder="dall-e-3">
                </div>
            </div>
        </div>

        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-4">{{ __('Per-Intent Model Overrides') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($intents as $intent => $model)
                <div>
                    <label class="block text-sm font-medium mb-1">{{ $intent }}</label>
                    <input type="text" name="intents[{{ $intent }}]"
                        value="{{ $model ?? '' }}"
                        class="w-full rounded-lg border px-3 py-2 text-sm bg-background"
                        placeholder="gpt-4o">
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border p-5 bg-card shadow-sm mb-6">
            <h2 class="font-semibold mb-3">{{ __('Provider Availability') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @foreach($aiConfig['providers'] ?? [] as $provider => $cfg)
                <div class="rounded-lg bg-muted/30 p-3 flex justify-between items-center">
                    <span class="font-medium">{{ $provider }}</span>
                    @php $hasKey = ! empty(env($cfg['key_env'] ?? '')); @endphp
                    <span class="{{ $hasKey ? 'text-green-600' : 'text-red-500' }} text-xs font-semibold">
                        {{ $hasKey ? '🔑 Key set' : '⚠ No key' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-primary text-primary-foreground px-6 py-2 text-sm font-medium hover:opacity-90">
            {{ __('Save Model Routing') }}
        </button>
    </form>
</div>
@endsection
