@php
    use App\Models\Issue / Support;use App\Models\User;use App\Scopes\ActiveScope;if (!isset($notification->data['subject'])) {
        $ticketReply = Issue / Support::find($notification->data['id']);
        $subject = $ticketReply->issue / support->subject;
    } else {
        $subject = $notification->data['subject'];
    }
    $notificationUser = User::withoutGlobalScope(ActiveScope::class)->find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification"
                          :link="route('issues / support.show', $notification->data['ticket_number'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.ticketReply.subject') . ' #' . $notification->data['ticket_number']"
                          :text="$subject"
                          :time="$notification->created_at"/>
@endif
