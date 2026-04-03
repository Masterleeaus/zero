@extends('layouts.app')
@section('title', $title ?? 'AIConverse')
@section('content')
<div class="container">
 <h3 class="mb-3">{{ $title ?? 'AIConverse' }}</h3>
 @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
 @yield('body')
</div>
@endsection
