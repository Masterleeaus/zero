@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('managedpremises::app.edit_inspection') }}</h3>
  @include('managedpremises::inspections.form', ['action' => route('managedpremises.inspections.update',[$property,$inspection]), 'method' => 'PUT', 'inspection' => $inspection])
</div>
@endsection
