<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $req->title }}</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-light">
  <div class="container py-5" style="max-width: 720px;">
    <div class="card">
      <div class="card-body">
        <h3 class="mb-2">{{ $req->title }}</h3>
        <div class="text-muted mb-3">{{ $req->message }}</div>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($req->status === 'cancelled')
          <div class="alert alert-warning">This request has been cancelled.</div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ route('documents.request.upload', $req->token) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Upload file</label>
            <input class="form-control" type="file" name="file" required>
            <div class="form-text">Max 25MB</div>
          </div>

          <button class="btn btn-primary">Upload</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
