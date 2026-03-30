@extends('layouts.app')

@section('content')
    <h4 class="mb-3">@lang('app.edit') @lang('propertymanagement::app.labels.property')</h4>

    @include('propertymanagement::properties.ajax.form', ['mode' => 'edit'])
@endsection
