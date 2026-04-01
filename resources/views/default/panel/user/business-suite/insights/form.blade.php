@extends('default.panel.layout.app')

@section('title', __('Performance Settings'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Performance Settings') }}</h1>
    </div>
    <p class="text-gray-500 text-sm">{{ __('Configure performance tracking preferences here.') }}</p>
</div>
@endsection
