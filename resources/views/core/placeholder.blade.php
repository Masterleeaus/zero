<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('Core') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 32px; width: min(720px, 92vw); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25); }
        h1 { margin: 0 0 8px; font-size: 28px; }
        p { margin: 0 0 12px; color: #cbd5e1; line-height: 1.6; }
        .badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #0ea5e9; color: #0b1729; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.04em; }
        small { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">{{ __('WorkCore merge') }}</div>
        <h1>{{ $title ?? __('Core') }}</h1>
        @if(!empty($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
        <p><small>{{ __('This screen is wired into the native core. Replace with domain UI as WorkCore assets are absorbed.') }}</small></p>
    </div>
</body>
</html>
