@extends('layouts.app')

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        @include('parking::parkir.ajax.show')
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection
