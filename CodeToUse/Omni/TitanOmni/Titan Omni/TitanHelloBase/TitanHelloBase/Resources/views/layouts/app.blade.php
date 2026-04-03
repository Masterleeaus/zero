@php
    $nav = $titanhelloNav['sidebar']['items'] ?? [];
@endphp
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @include('titanhello::partials.flash')
            @yield('titanhello-content')
        </div>
    </div>
</div>
@endsection
