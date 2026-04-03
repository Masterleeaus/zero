@php
    use App\ProjectMember;if (!isset($notification->data['site'])) {
        $site = ProjectMember::with('site')->find($notification->data['id']);
        $projectId = $site->project_id;
        $site = $site->site->project_name;
    } else {
        $projectId = $notification->data['project_id'];
        $site = $notification->data['site'];
    }
@endphp

<x-cards.notification :notification="$notification" :link="route('sites.show', $projectId)"
                      :image="user()->image_url"
                      :title="__('email.newProjectMember.mentionProject')" :text="$site"
                      :time="$notification->created_at"/>
