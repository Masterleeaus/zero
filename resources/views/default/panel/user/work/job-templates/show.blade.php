@extends('panel.layout.app')
@section('title', __('Job Template') . ': ' . $template->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.work.job-templates.edit', $template) }}" variant="secondary">
        <x-tabler-pencil class="size-4" />
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <x-card>
            <dl class="grid md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Name') }}</dt>
                    <dd class="font-medium">{{ $template->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Job Type') }}</dt>
                    <dd>{{ optional($template->jobType)->name ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Team') }}</dt>
                    <dd>{{ optional($template->team)->name ?? '–' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Default Duration') }}</dt>
                    <dd>{{ $template->duration }} {{ __('h') }}</dd>
                </div>
            </dl>
            @if($template->instructions)
                <div class="mt-4 text-sm">
                    <p class="text-slate-500 mb-1">{{ __('Instructions') }}</p>
                    <p class="whitespace-pre-wrap">{{ $template->instructions }}</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection
