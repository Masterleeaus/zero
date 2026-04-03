@php
    $nav = $titantalkNav['sidebar']['items'] ?? [];
@endphp
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('titantalk::partials.flash')
            @yield('titantalk-content')
        </div>
    </div>
</div>
@endsection
