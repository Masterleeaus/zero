@extends('panel.layout.app')
@section('title', $account->name)
@section('titlebar_actions')
    <x-button href="{{ route('dashboard.money.accounts.edit', $account) }}" variant="secondary">
        {{ __('Edit') }}
    </x-button>
@endsection

@section('content')
    <div class="py-6 space-y-4">
        <dl class="grid md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Code') }}</dt>
                <dd class="mt-1 font-mono">{{ $account->code ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Type') }}</dt>
                <dd class="mt-1 capitalize">{{ $account->type }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    <x-badge variant="{{ $account->is_active ? 'success' : 'secondary' }}">
                        {{ $account->is_active ? __('Active') : __('Inactive') }}
                    </x-badge>
                </dd>
            </div>
            @if($account->description)
                <div class="md:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                    <dd class="mt-1 text-sm">{{ $account->description }}</dd>
                </div>
            @endif
        </dl>

        @if($account->children->isNotEmpty())
            <div>
                <h3 class="text-sm font-semibold mb-2">{{ __('Sub-accounts') }}</h3>
                <ul class="space-y-1">
                    @foreach($account->children as $child)
                        <li>
                            <a href="{{ route('dashboard.money.accounts.show', $child) }}" class="text-blue-600 hover:underline text-sm">
                                {{ $child->code ? "[{$child->code}] " : '' }}{{ $child->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
