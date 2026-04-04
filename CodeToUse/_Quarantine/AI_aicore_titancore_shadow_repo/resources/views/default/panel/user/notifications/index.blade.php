@extends('panel.layout.app')
@section('title', __('Notifications'))

@section('content')
    <div class="py-6 space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">{{ __('Your notifications') }}</h2>
            <form method="post" action="{{ route('dashboard.notifications.read') }}">
                @csrf
                <x-button type="submit" variant="secondary" size="sm">
                    {{ __('Mark all as read') }}
                </x-button>
            </form>
        </div>
        <x-card>
            <div class="divide-y divide-slate-200">
                @forelse($notifications as $notification)
                    <div class="py-3 flex items-start gap-3 @if(is_null($notification->read_at)) bg-slate-50 @endif">
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <div class="font-semibold">{{ data_get($notification->data, 'title') ?? __('Notification') }}</div>
                                <span class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ data_get($notification->data, 'message') ?? data_get($notification->data, 'body') }}</p>
                        </div>
                        @if(is_null($notification->read_at))
                            <form method="post" action="{{ route('dashboard.notifications.read') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $notification->id }}">
                                <x-button type="submit" size="sm" variant="ghost">{{ __('Mark read') }}</x-button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-500">{{ __('No notifications yet.') }}</div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        </x-card>
    </div>
@endsection
