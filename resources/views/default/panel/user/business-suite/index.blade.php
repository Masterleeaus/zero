@extends('default.panel.layout.app')

@section('title', __('TitanBOS'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('TitanBOS — Command Center') }}</h1>
    </div>
    <p class="text-gray-500">{{ __('Your TitanBOS operational workspace is ready. Select a section from the menu to get started.') }}</p>
</div>
@endsection
