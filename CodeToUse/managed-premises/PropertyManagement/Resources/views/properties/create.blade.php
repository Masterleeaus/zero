@extends('layouts.app')

@section('content')
    <h4 class="mb-3">@lang('app.add') @lang('propertymanagement::app.labels.property')</h4>

    @include('propertymanagement::properties.ajax.form', ['mode' => 'create'])
@endsection
