@extends('panel.layout.app')
@section('title', $account->exists ? __('Edit Account') : __('New Account'))

@section('content')
    <div class="py-6 max-w-2xl">
        <form method="POST"
              action="{{ $account->exists ? route('dashboard.money.accounts.update', $account) : route('dashboard.money.accounts.store') }}">
            @csrf
            @if($account->exists)
                @method('PUT')
            @endif

            <div class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-form.group>
                        <x-form.label for="code">{{ __('Account Code') }}</x-form.label>
                        <x-form.input id="code" name="code" value="{{ old('code', $account->code) }}" placeholder="e.g. 1100" />
                        <x-form.error field="code" />
                    </x-form.group>

                    <x-form.group>
                        <x-form.label for="type">{{ __('Type') }}</x-form.label>
                        <x-select id="type" name="type">
                            @foreach($types as $t)
                                <option value="{{ $t }}" @selected(old('type', $account->type) === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </x-select>
                        <x-form.error field="type" />
                    </x-form.group>
                </div>

                <x-form.group>
                    <x-form.label for="name">{{ __('Name') }}</x-form.label>
                    <x-form.input id="name" name="name" value="{{ old('name', $account->name) }}" required />
                    <x-form.error field="name" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="description">{{ __('Description') }}</x-form.label>
                    <x-form.textarea id="description" name="description" rows="3">{{ old('description', $account->description) }}</x-form.textarea>
                    <x-form.error field="description" />
                </x-form.group>

                <x-form.group>
                    <x-form.label for="parent_id">{{ __('Parent Account') }}</x-form.label>
                    <x-select id="parent_id" name="parent_id">
                        <option value="">{{ __('None') }}</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $account->parent_id) == $parent->id)>
                                {{ $parent->code ? "[{$parent->code}] " : '' }}{{ $parent->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-form.error field="parent_id" />
                </x-form.group>

                <x-form.group>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true)) />
                        {{ __('Active') }}
                    </label>
                </x-form.group>
            </div>

            <div class="mt-6 flex gap-3">
                <x-button type="submit">{{ __('Save') }}</x-button>
                <x-button href="{{ route('dashboard.money.accounts.index') }}" variant="ghost">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </div>
@endsection
