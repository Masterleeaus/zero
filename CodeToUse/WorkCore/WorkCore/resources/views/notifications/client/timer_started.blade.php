<x-cards.notification :notification="$notification"  :link="route('sites.show', $notification->data['site']['id'])" :image="user()->image_url"
    :title="__('modules.service jobs.timerStartedProject')" :text="$notification->data['site']['project_name']"
    :time="$notification->created_at" />
