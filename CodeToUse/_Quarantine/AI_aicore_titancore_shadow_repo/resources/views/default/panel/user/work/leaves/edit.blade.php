@extends('panel.layout.app')
@section('title', __('Edit Leave'))

@section('content')
    <div class="py-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ __('Edit Leave') }}</h2>
        <form method="post" action="{{ route('dashboard.work.leaves.update', $leave) }}" class="space-y-4">
            @method('put')
            @include('default.panel.user.work.leaves.form')
            <x-button type="submit">{{ __('Update') }}</x-button>
        </form>

        <form method="post" action="{{ route('dashboard.work.leaves.destroy', $leave) }}">
            @csrf
            @method('delete')
            <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
        </form>
    </div>
@endsection
