<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contract {{ $contract->number }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  @include('contracts::partials.brand')
  <div class="card">
    <div class="card-body">
      <h3 class="mb-1">{{ $contract->title }}</h3>
      <div class="text-muted mb-3">Contract #{{ $contract->number }}</div>
      <div class="mb-3">{!! $ver->body_html !!}</div>

      <h5>Sign</h5>
      <form method="post" action="{{ route('contracts.public.sign', $contract->id) }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-4"><input name="name" class="form-control" placeholder="Your full name" required></div>
          <div class="col-md-4"><input name="email" type="email" class="form-control" placeholder="you@example.com" required></div>
          <div class="col-md-4"><input name="signature" class="form-control" placeholder="Type your signature"></div>
        </div>
        <button class="btn btn-success mt-3">I Agree & Sign</button>
      </form>

      <form class="mt-2" method="post" action="{{ route('contracts.public.decline', $contract->id) }}">
        @csrf
        <div class="row g-2 align-items-center">
          <div class="col"><input name="reason" class="form-control" placeholder="Reason (optional)"></div>
          <div class="col-auto"><button class="btn btn-outline-danger">Decline</button></div>
        </div>
      </form>

      @if(!empty(config('contracts.brand.footer_note')))
      <p class="mt-4 text-muted small">{{ config('contracts.brand.footer_note') }}</p>
      @endif
    </div>
  </div>
</div>
</body>
</html>
