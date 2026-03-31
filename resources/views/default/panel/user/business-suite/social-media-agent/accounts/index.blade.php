@php
    $theme = get_theme();
@endphp

@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Client Accounts'))
@section('titlebar_subtitle', __('Connect and manage client accounts that drafts and programs belong to.'))
@section('titlebar_actions')
    @include('social-media-agent::components.titlebar-actions')
@endsection

@section('content')
    <div @class(['px-5', 'py-5' => $theme !== 'social-media-agent-dashboard'])>
        @include('social-media-agent::accounts.banner')
        @include('social-media-agent::accounts.platform-cards', ['platforms' => $platforms])
        @include('social-media-agent::accounts.platforms-table', ['platforms' => $userPlatforms])
    </div>
@endsection
