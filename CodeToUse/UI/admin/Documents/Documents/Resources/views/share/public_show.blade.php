<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $document->title ?? 'Shared document' }}</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .meta { color:#666; font-size: 12px; margin-bottom: 12px;}
    pre { white-space: pre-wrap; }
    .badge { display:inline-block; padding:2px 8px; border:1px solid #ddd; border-radius: 10px; font-size:12px;}
  </style>
</head>
<body>
  <h1>{{ $document->title ?? 'Document' }}</h1>
  <div class="meta">
    <span class="badge">{{ strtoupper($document->type ?? 'GENERAL') }}</span>
    @if($link->expires_at) • Expires {{ $link->expires_at->format('Y-m-d H:i') }} @endif
  </div>
  <pre>{{ $document->content }}</pre>
</body>
</html>
