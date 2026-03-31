@extends('default.panel.layout.app')

@section('title', __('Business Suite'))

@section('content')
<div class="flex flex-col gap-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-heading">{{ __('Business Suite') }}</h1>
    </div>
    <p class="text-gray-500">{{ __('Business Suite features are being configured for your account.') }}</p>
</div>
@endsection
