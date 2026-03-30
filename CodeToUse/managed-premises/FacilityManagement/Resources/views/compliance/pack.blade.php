<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Compliance Pack — {{ $building->name }}</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    h1, h2 { margin: 0 0 8px; }
    .section { margin-top: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f4f4f4; text-align: left; }
    .muted { color: #666; }
  </style>
</head>
<body>
  <h1>Compliance Pack</h1>
  <div class="muted">Building: <strong>{{ $building->name }}</strong> (ID {{ $building->id }})</div>

  <div class="section">
    <h2>Documents</h2>
    <table>
      <thead><tr><th>Type</th><th>Issued</th><th>Expires</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($docs as $d)
          <tr>
            <td>{{ $d->doc_type }}</td>
            <td>{{ $d->issued_at }}</td>
            <td>{{ $d->expires_at }}</td>
            <td>{{ $d->status }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="muted">No documents found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Inspections (recent)</h2>
    <table>
      <thead><tr><th>ID</th><th>Status</th><th>Scheduled</th><th>Completed</th></tr></thead>
      <tbody>
        @forelse($ins as $i)
          <tr>
            <td>{{ $i->id }}</td>
            <td>{{ $i->status }}</td>
            <td>{{ $i->scheduled_at }}</td>
            <td>{{ $i->completed_at }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="muted">No inspections found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="section muted">
    Generated {{ now() }}
  </div>
</body>
</html>
