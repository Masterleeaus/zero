<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $doc->title }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;margin:24px;color:#222;}
    h1{font-size:24px;margin-bottom:4px;}
    .muted{color:#666;font-size:12px;margin-bottom:16px;}
    pre{white-space:pre-wrap;background:#f8f9fa;padding:16px;border-radius:6px;border:1px solid #dee2e6;}
    ul.attachments{margin-top:16px;padding-left:18px;}
    ul.attachments li{margin-bottom:4px;}
  </style>
</head>
<body>
  <h1>{{ $doc->title }}</h1>
  <div class="muted">
    {{ __('Type') }}: {{ strtoupper($doc->type ?? 'general') }} |
    {{ __('Category') }}: {{ $doc->category ?? '—' }} |
    {{ __('Updated') }}: {{ optional($doc->updated_at)->format('d M Y H:i') }}
  </div>

  <pre>{{ $doc->body_markdown }}</pre>

  @if($doc->files && $doc->files->where('is_public', true)->count())
    <h3>{{ __('Attachments') }}</h3>
    <ul class="attachments">
      @foreach($doc->files->where('is_public', true) as $file)
        <li>
          <a href="{{ route('documents.attachments.download', $file->id) }}">
            {{ $file->name }}
          </a>
          <span class="muted">
            ({{ number_format($file->size / 1024, 1) }} KB)
          </span>
        </li>
      @endforeach
    </ul>
  @endif
</body>
</html>
