<x-cards.notification :notification="$notification" :link="route('service jobs.index')" :image="user()->image_url"
                      :title="__('email.trackerReminder.subject') . ' #' . $notification->data['id']"
                      :time="$notification->created_at"/>
