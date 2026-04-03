@csrf
<x-form.group>
    <x-form.label for="name">{{ __('Name') }}</x-form.label>
    <x-form.input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required />
</x-form.group>

<x-form.group>
    <x-form.label for="description">{{ __('Description') }}</x-form.label>
    <x-form.textarea id="description" name="description" rows="3">{{ old('description', $category->description ?? '') }}</x-form.textarea>
</x-form.group>
