@extends('titanhello::layouts.master')
@section('content')

<h4 class="mb-3">Create Dial Campaign</h4>
@include('titanhello::partials.flash')

<form method="POST" action="{{ route('titanhello.campaigns.store') }}" class="card card-body">
  @csrf
  @include('titanhello::campaigns/form', ['campaign' => null])
  <div class="mt-3">
    <button class="btn btn-primary">Create</button>
    <a href="{{ route('titanhello.campaigns.index') }}" class="btn btn-light">Cancel</a>
  </div>
</form>

@endsection
