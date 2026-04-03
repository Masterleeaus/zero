<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $tpl->name }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;margin:32px;color:#222;}
    h1{font-size:24px;margin-bottom:8px;}
    .muted{color:#666;font-size:12px;margin-bottom:16px;}
    pre{white-space:pre-wrap;background:#f8f9fa;padding:16px;border-radius:6px;border:1px solid #dee2e6;}
  </style>
</head>
<body>
  <h1>{{ $tpl->name }}</h1>
  <div class="muted">
    {{ __('Category') }}: {{ $tpl->category ?? '—' }} |
    {{ __('Updated') }}: {{ optional($tpl->updated_at)->format('d M Y H:i') }}
  </div>
  <pre>{{ $tpl->body_markdown }}</pre>
</body>
</html>
