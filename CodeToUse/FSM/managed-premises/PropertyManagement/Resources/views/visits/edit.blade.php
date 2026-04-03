@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.edit_visit') }}</h3>
  @include('propertymanagement::visits.form', ['action' => route('propertymanagement.visits.update',[$property,$visit]), 'method' => 'PUT', 'visit' => $visit])
</div>
@endsection
