@extends('chatbot::overlay.layout')
@section('content')
<div class="card"><h1>{{ $chatbot->title }} Analytics</h1><pre>{{ print_r($stats, true) }}</pre></div>
@endsection