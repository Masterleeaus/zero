@php
    use App\Models\User;$notificationUser = User::find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification"
                          :link="route('issues / support.show', $notification->data['ticket_number'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.newTicketRequester.subject') . ' #' . $notification->data['ticket_number']"
                          :text="$notification->data['subject']" :time="$notification->created_at"/>
@endif
