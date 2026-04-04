@csrf
<x-form.group>
    <x-form.label for="user_id">{{ __('User') }}</x-form.label>
    <x-form.select name="user_id" id="user_id" required>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', optional($leave)->user_id) == $user->id)>{{ $user->name }}</option>
        @endforeach
    </x-form.select>
</x-form.group>

<x-form.group>
    <x-form.label for="type">{{ __('Leave Type') }}</x-form.label>
    <x-form.select name="type" id="type" required>
        @foreach($types as $type)
            <option value="{{ $type }}" @selected(old('type', optional($leave)->type ?? 'annual') === $type)>{{ __(ucfirst($type)) }}</option>
        @endforeach
    </x-form.select>
</x-form.group>

<x-form.group>
    <x-form.label for="status">{{ __('Status') }}</x-form.label>
    <x-form.input type="text" name="status" id="status" value="{{ old('status', optional($leave)->status ?? 'approved') }}" required />
</x-form.group>

<div class="grid md:grid-cols-2 gap-4">
    <x-form.group>
        <x-form.label for="start_date">{{ __('Start date') }}</x-form.label>
        <x-form.input type="date" name="start_date" id="start_date" value="{{ old('start_date', optional(optional($leave)->start_date ?? now())->format('Y-m-d')) }}" required />
    </x-form.group>
    <x-form.group>
        <x-form.label for="end_date">{{ __('End date') }}</x-form.label>
        <x-form.input type="date" name="end_date" id="end_date" value="{{ old('end_date', optional(optional($leave)->end_date ?? now())->format('Y-m-d')) }}" required />
    </x-form.group>
</div>

<x-form.group>
    <x-form.label for="reason">{{ __('Reason (optional)') }}</x-form.label>
    <x-form.textarea name="reason" id="reason" rows="3">{{ old('reason', optional($leave)->reason ?? '') }}</x-form.textarea>
</x-form.group>
