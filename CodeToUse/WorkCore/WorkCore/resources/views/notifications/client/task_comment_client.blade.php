<x-cards.notification :notification="$notification"  :link="route('service jobs.show', $notification->data['id']).'?view=comments'" :image="user()->image_url"
    :title="__('email.taskComment.subject')" :text="$notification->data['heading']"
    :time="$notification->created_at" />
