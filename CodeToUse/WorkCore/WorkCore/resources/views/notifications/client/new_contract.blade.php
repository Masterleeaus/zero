<x-cards.notification :notification="$notification"  :link="route('service agreements.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.newContract.subject')" :time="$notification->created_at" />
