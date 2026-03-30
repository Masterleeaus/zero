@extends('layouts.app')
@section('content')
<div class="content-wrapper">
  <h4 class="mb-3">@lang('propertymanagement::app.overview') - {{ $property->name }}</h4>
  @include('propertymanagement::partials.property-tabs', ['property'=>$property])
  @include('propertymanagement::widgets.property-profile', ['property'=>$property])
</div>
@endsection
