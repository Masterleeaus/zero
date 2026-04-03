@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.add_inspection') }}</h3>
  @include('propertymanagement::inspections.form', ['action' => route('propertymanagement.inspections.store',$property), 'method' => 'POST'])
</div>
@endsection
