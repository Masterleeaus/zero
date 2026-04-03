@php
    $site = \App\Models\Site::find($notification->data['id']);
@endphp

<x-cards.notification :notification="$notification" :link="route('sites.show', $notification->data['id']).'?tab=rating'"
                      :image="user()->image_url"
                      :title="__('email.projectRating.subject')" :text="$site->project_name"
                      :time="$notification->created_at"/>
