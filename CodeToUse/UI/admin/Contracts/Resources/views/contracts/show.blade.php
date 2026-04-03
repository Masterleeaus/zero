@extends('layouts.app')
@section('content')
<div class="container py-4">
  @include('contracts::partials.brand')
  <div class="d-flex justify-content-between">
    <h3>Contract {{ $contract->number }}</h3>
    <form method="post" action="{{ route('contracts.send', $contract->id) }}">
      @csrf <button class="btn btn-sm btn-primary">Send to Signers</button>
    </form>
  </div>
  <p class="text-muted">Status: {{ $contract->status }} &middot; Client: {{ $contract->client_id ?? '-' }}</p>
  <div class="card mt-3">
    <div class="card-body">
      {!! optional($contract->versions->last())->body_html !!}
    </div>
  </div>
  <h5 class="mt-4">Signers</h5>
  <ul>
    @foreach($contract->signers as $s)
      <li>{{ $s->name }} &lt;{{ $s->email }}&gt; — {{ $s->role }} @if($s->signed_at) ✅ signed {{ $s->signed_at->diffForHumans() }} @endif</li>
    @endforeach
  </ul>
</div>
@endsection
