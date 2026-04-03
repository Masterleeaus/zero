@extends('layouts.app')

@section('title', $title ?? trim($__env->yieldContent('title')) ?: 'Titan Talk')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @yield('content')
</div>
@endsection
