<x-card>
    <x-slot name="header">@lang('propertymanagement::app.module_name')</x-slot>
    <x-slot name="body">
        <div class="small text-muted">@lang('propertymanagement::app.address')</div>
        <div>{{ $property->address }}</div>
        <div class="mt-2 small text-muted">@lang('propertymanagement::app.access_notes')</div>
        <div>{{ $property->access_notes }}</div>
        <div class="mt-2 small text-muted">@lang('propertymanagement::app.hazards')</div>
        <div>{{ $property->hazards }}</div>
    </x-slot>
</x-card>
