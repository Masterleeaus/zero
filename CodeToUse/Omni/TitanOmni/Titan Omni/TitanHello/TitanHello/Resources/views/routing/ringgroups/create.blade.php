@extends('titanhello::layouts.master')
@section('content')

<h4 class="mb-3">Create Ring Group</h4>
@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.routing.ringgroups.store') }}" class="card card-body">
  @csrf
  @include('titanhello::routing/ringgroups/form', ['group' => null])
  <div class="mt-3">
    <button class="btn btn-primary">Create</button>
    <a href="{{ route('titanhello.routing.ringgroups.index') }}" class="btn btn-light">Cancel</a>
  </div>
</form>

@endsection
