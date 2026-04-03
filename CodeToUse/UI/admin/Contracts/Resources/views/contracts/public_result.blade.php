<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Contract {{ $contract->number }} – {{ ucfirst($result) }}</title></head>
<body class="bg-light">
<div class="container py-4">
  @include('contracts::partials.brand')
  <div class="card"><div class="card-body">
    <h3>Contract {{ $contract->number }}</h3>
    @if($result === 'declined')
      <div class="alert alert-secondary">This contract was <strong>declined</strong>.</div>
    @endif
  </div></div>
</div>
</body>
</html>
