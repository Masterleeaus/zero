<x-cards.notification :notification="$notification" :link="route('service jobs.show', $notification->data['id'])"
                      :image="user()->image_url"
                      :title="__('email.subTaskComplete.subject')"
                      :time="$notification->created_at"/>
