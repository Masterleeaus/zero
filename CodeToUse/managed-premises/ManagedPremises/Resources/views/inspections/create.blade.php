@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('managedpremises::app.add_inspection') }}</h3>
  @include('managedpremises::inspections.form', ['action' => route('managedpremises.inspections.store',$property), 'method' => 'POST'])
</div>
@endsection
