@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.edit_service_plan') }}</h3>
  @include('propertymanagement::service_plans.form', ['action' => route('propertymanagement.service-plans.update',[$property,$plan]), 'method' => 'PUT', 'plan' => $plan])
</div>
@endsection
