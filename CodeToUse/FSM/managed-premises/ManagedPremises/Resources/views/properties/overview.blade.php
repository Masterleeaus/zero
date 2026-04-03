@extends('layouts.app')
@section('content')
<div class="content-wrapper">
  <h4 class="mb-3">@lang('managedpremises::app.overview') - {{ $property->name }}</h4>
  @include('managedpremises::partials.property-tabs', ['property'=>$property])
  @include('managedpremises::widgets.property-profile', ['property'=>$property])
</div>
@endsection
