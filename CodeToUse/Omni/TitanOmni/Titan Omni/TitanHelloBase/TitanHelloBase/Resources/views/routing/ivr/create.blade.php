@extends('titanhello::layouts.master')
@section('content')

<h4 class="mb-3">Create IVR Menu</h4>
@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.ivr.store') }}" class="card card-body">
  @csrf
  @include('titanhello::routing/ivr/form', ['menu' => null])
  <div class="mt-3">
    <button class="btn btn-primary">Create</button>
    <a href="{{ route('titanhello.routing.ivr.index') }}" class="btn btn-light">Cancel</a>
  </div>
</form>

@endsection
