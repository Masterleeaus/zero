@php
    use App\Models\Site;$notificationUser = Site::find($notification->data['id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('sites.show', $notification->data['id'])"
                          :image="$notificationUser->customer->image_url" :title="__('email.rating.subject')"
                          :text="$notification->data['project_name']" :time="$notification->created_at"/>
@endif
