@extends('titanhello::layouts.master')
@section('content')

<h4 class="mb-3">Edit Inbound Number</h4>
@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.numbers.update', $number->id) }}" class="card card-body">
  @csrf
  @include('titanhello::routing/numbers/form', ['number' => $number])
  <div class="mt-3">
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('titanhello.routing.numbers.index') }}" class="btn btn-light">Back</a>
  </div>
</form>

@endsection
