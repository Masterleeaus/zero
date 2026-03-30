@extends('layouts.app')
@section('content')
<div class="container">
  <h3>{{ __('propertymanagement::app.add_visit') }}</h3>
  @include('propertymanagement::visits.form', ['action' => route('propertymanagement.visits.store',$property), 'method' => 'POST'])
</div>
@endsection
