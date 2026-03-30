@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.edit_inspection') }}</h3>
  @include('propertymanagement::inspections.form', ['action' => route('propertymanagement.inspections.update',[$property,$inspection]), 'method' => 'PUT', 'inspection' => $inspection])
</div>
@endsection
