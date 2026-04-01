@csrf
<x-form.group>
    <x-form.label for="title">{{ __('Title') }}</x-form.label>
    <x-form.input type="text" id="title" name="title" value="{{ old('title', $expense->title ?? '') }}" required />
</x-form.group>

<x-form.group>
    <x-form.label for="expense_category_id">{{ __('Category') }}</x-form.label>
    <x-form.select name="expense_category_id" id="expense_category_id">
        <option value="">{{ __('Uncategorised') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('expense_category_id', $expense->expense_category_id ?? '') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </x-form.select>
</x-form.group>

<div class="grid md:grid-cols-2 gap-4">
    <x-form.group>
        <x-form.label for="amount">{{ __('Amount') }}</x-form.label>
        <x-form.input type="number" step="0.01" min="0" id="amount" name="amount" value="{{ old('amount', $expense->amount ?? 0) }}" required />
    </x-form.group>
    <x-form.group>
        <x-form.label for="expense_date">{{ __('Date') }}</x-form.label>
        <x-form.input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date ?? now())->format('Y-m-d')) }}" />
    </x-form.group>
</div>

<x-form.group>
    <x-form.label for="notes">{{ __('Notes') }}</x-form.label>
    <x-form.textarea id="notes" name="notes" rows="3">{{ old('notes', $expense->notes ?? '') }}</x-form.textarea>
</x-form.group>
