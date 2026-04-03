@php
    $branding = app(\Modules\Documents\Services\Pdf\PdfBrandingService::class);
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document #{{ $document->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .header { border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 12px;}
        .footer { border-top: 1px solid #ddd; padding-top: 8px; margin-top: 12px; font-size: 10px; color: #666;}
        .section { margin-bottom: 10px; }
        h1 { font-size: 18px; margin: 0; }
        h2 { font-size: 14px; margin: 12px 0 6px; }
        .muted { color: #666; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ $document->title ?? ('Document '.$document->id) }}</h1>
    <div class="muted">{{ $branding->headerTitle() }} • {{ strtoupper($document->type ?? 'GENERAL') }}</div>
</div>

<div class="section">
    <h2>Content</h2>
    <pre>{{ $document->content }}</pre>
</div>

<div class="footer">
    {{ $branding->footerText() }} • {{ now()->format('Y-m-d H:i') }}
</div>
</body>
</html>
