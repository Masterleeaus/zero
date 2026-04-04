<div class="grid md:grid-cols-2 gap-4">
    <x-form.group class="md:col-span-2">
        <x-form.label for="name">{{ __('Asset Name') }} <span class="text-red-500">*</span></x-form.label>
        <x-form.input id="name" name="name" value="{{ old('name', $asset->name ?? '') }}" required />
        <x-form.error field="name" />
    </x-form.group>

    <x-form.group>
        <x-form.label for="category">{{ __('Category') }}</x-form.label>
        <x-form.input id="category" name="category" value="{{ old('category', $asset->category ?? '') }}" />
        <x-form.error field="category" />
    </x-form.group>

    <x-form.group>
        <x-form.label for="acquisition_date">{{ __('Acquisition Date') }} <span class="text-red-500">*</span></x-form.label>
        <x-form.input type="date" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->toDateString() ?? '') }}" required />
        <x-form.error field="acquisition_date" />
    </x-form.group>

    <x-form.group>
        <x-form.label for="acquisition_cost">{{ __('Acquisition Cost') }} <span class="text-red-500">*</span></x-form.label>
        <x-form.input type="number" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost', $asset->acquisition_cost ?? '0') }}" min="0" step="0.01" required />
        <x-form.error field="acquisition_cost" />
    </x-form.group>

    <x-form.group>
        <x-form.label for="depreciation_rate">{{ __('Annual Depreciation Rate') }} (0–1) <span class="text-red-500">*</span></x-form.label>
        <x-form.input type="number" id="depreciation_rate" name="depreciation_rate" value="{{ old('depreciation_rate', $asset->depreciation_rate ?? '0') }}" min="0" max="1" step="0.0001" required />
        <p class="text-xs text-gray-500">{{ __('e.g. 0.2 = 20% per year (straight-line)') }}</p>
        <x-form.error field="depreciation_rate" />
    </x-form.group>

    <x-form.group class="md:col-span-2">
        <x-form.label for="description">{{ __('Description') }}</x-form.label>
        <x-form.textarea id="description" name="description">{{ old('description', $asset->description ?? '') }}</x-form.textarea>
    </x-form.group>

    <x-form.group class="md:col-span-2">
        <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
        <x-form.textarea id="notes" name="notes">{{ old('notes', $asset->notes ?? '') }}</x-form.textarea>
    </x-form.group>
</div>
