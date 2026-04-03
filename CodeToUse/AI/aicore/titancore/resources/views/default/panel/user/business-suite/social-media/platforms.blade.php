@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Contacts'))
@section('titlebar_actions', '')
@section('titlebar_subtitle', __('Connect and manage client contacts / accounts for programs and drafts.'))

@section('content')
	<div class="py-10">
		@include('social-media::platforms.platform-statistics', ['items' => $userPlatforms])
		@include('social-media::platforms.platform-cards', ['platforms' => $platforms])
		@include('social-media::platforms.platform-table', ['items' => $userPlatforms])
	</div>
@endsection
@push('script')
@endpush
