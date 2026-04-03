<x-cards.notification :notification="$notification"
                      :link="route('service jobs.show', $notification->data['id']) . '?view=sub_task'"
                      :image="user()->image_url"
                      :title="__('email.subTaskCreated'). ' - '. __('app.service job').'#'.$notification->data['id']"
                      :time="$notification->created_at"/>
