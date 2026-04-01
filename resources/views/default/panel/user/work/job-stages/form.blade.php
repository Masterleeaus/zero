@extends('panel.layout.app')
@section('title', $stage->exists ? __('Edit Job Stage') : __('New Job Stage'))

@section('content')
    <div class="py-6">
        <form method="post"
              action="{{ $stage->exists ? route('dashboard.work.job-stages.update', $stage) : route('dashboard.work.job-stages.store') }}"
              class="space-y-4">
            @csrf
            @if($stage->exists)
                @method('put')
            @endif

            <x-card>
                <div class="grid md:grid-cols-2 gap-4">
                    <x-input label="{{ __('Name') }}" name="name" required value="{{ old('name', $stage->name) }}" />

                    <x-select label="{{ __('Stage Type') }}" name="stage_type">
                        @foreach($stageTypes as $t)
                            <option value="{{ $t }}" @selected(old('stage_type', $stage->stage_type) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </x-select>

                    <x-input label="{{ __('Sequence') }}" type="number" name="sequence" value="{{ old('sequence', $stage->sequence ?? 1) }}" min="0" />

                    <x-input label="{{ __('Color (hex)') }}" name="color" value="{{ old('color', $stage->color ?? '#FFFFFF') }}" maxlength="7" />

                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $stage->is_default)) class="rounded" />
                            {{ __('Default stage') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_closed" value="0">
                            <input type="checkbox" name="is_closed" value="1" @checked(old('is_closed', $stage->is_closed)) class="rounded" />
                            {{ __('Closed stage') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="fold" value="0">
                            <input type="checkbox" name="fold" value="1" @checked(old('fold', $stage->fold)) class="rounded" />
                            {{ __('Folded in kanban') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="require_signature" value="0">
                            <input type="checkbox" name="require_signature" value="1" @checked(old('require_signature', $stage->require_signature)) class="rounded" />
                            {{ __('Require signature') }}
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <x-textarea label="{{ __('Description') }}" name="description" rows="3">{{ old('description', $stage->description) }}</x-textarea>
                </div>
            </x-card>

            <div class="flex gap-3">
                <x-button type="submit">
                    <x-tabler-check class="size-4" />
                    {{ $stage->exists ? __('Update') : __('Create') }}
                </x-button>
                <x-button type="button"
                          href="{{ $stage->exists ? route('dashboard.work.job-stages.show', $stage) : route('dashboard.work.job-stages.index') }}"
                          variant="secondary">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
@endsection
