@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.add_service_plan') }}</h3>
  @include('propertymanagement::service_plans.form', ['action' => route('propertymanagement.service-plans.store',$property), 'method' => 'POST'])
</div>
@endsection
