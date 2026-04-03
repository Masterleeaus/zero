<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Chatbot Overlay' }}</title>
<style>body{font-family:Arial,sans-serif;margin:24px;background:#f7f7f9;color:#222}table{width:100%;border-collapse:collapse;background:#fff}th,td{border:1px solid #ddd;padding:8px;text-align:left}a{color:#0a58ca;text-decoration:none}.card{background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px;margin-bottom:16px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}.muted{color:#666}.messages{background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px}.msg{padding:10px;border-bottom:1px solid #eee}.msg:last-child{border-bottom:none}textarea{width:100%}</style>
</head>
<body>@yield('content')</body></html>