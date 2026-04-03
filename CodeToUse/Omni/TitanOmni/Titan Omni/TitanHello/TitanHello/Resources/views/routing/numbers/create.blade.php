@extends('titanhello::layouts.master')
@section('content')

<h4 class="mb-3">Add Inbound Number</h4>
@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.numbers.store') }}" class="card card-body">
  @csrf
  @include('titanhello::routing/numbers/form', ['number' => null])
  <div class="mt-3">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('titanhello.routing.numbers.index') }}" class="btn btn-light">Cancel</a>
  </div>
</form>

@endsection
