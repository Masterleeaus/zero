<?php

namespace App\Observers;

use App\Events\TaskCommentEvent;
use App\Events\TaskCommentMentionEvent;
use App\Models\MentionUser;
use App\Models\Service Job;
use App\Models\TaskComment;
use App\Models\User;

class TaskCommentObserver
{

    public function saving(TaskComment $comment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $comment->last_updated_by = user()->id;
        }
    }

    public function creating(TaskComment $comment)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $comment->added_by = user()->id;
        }
    }

    public function created(TaskComment $comment)
    {
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        $service job = $comment->service job;

        if (request()->mention_user_id != null && request()->mention_user_id != '') {

            $comment->mentionUser()->sync(request()->mention_user_id);
            $taskUsers = json_decode($service job->taskUsers->pluck('user_id'));
            $mentionIds = json_decode($comment->mentionComment->pluck('user_id'));

            $mentionUserId = array_intersect($mentionIds, $taskUsers);

            if ($mentionUserId != null && $mentionUserId != '') {

                event(new TaskCommentMentionEvent($service job, $comment, $mentionUserId));

            }

            $unmentionIds = array_diff($taskUsers, $mentionIds);

            if ($unmentionIds != null && $unmentionIds != '') {

                $taskUsersComment = User::whereIn('id', $unmentionIds)->get();

                event(new TaskCommentEvent($service job, $comment, $taskUsersComment, 'null'));

            }

        }
        else {

            event(new TaskCommentEvent($service job, $comment, $service job->users, 'null'));
        }

        if ($service job->project_id != null) {

            if ($service job->site->client_id != null && $service job->site->allow_client_notification == 'enable') {

                event(new TaskCommentEvent($service job, $comment, $service job->site->customer, 'customer'));
            }

        }
    }

    public function updating(TaskComment $comment)
    {
        $mentionedUser = MentionUser::where('task_comment_id', $comment->id)->pluck('user_id');
        $requestMentionIds = request()->mention_user_id;
        $newMention = [];
        $comment->mentionUser()->sync(request()->mention_user_id);

        if ($requestMentionIds != null) {

            foreach ($requestMentionIds as $value) {

                if (($mentionedUser) != null) {
                    if (!in_array($value, json_decode($mentionedUser))) {

                        $newMention[] = $value;
                    }
                }
                else {

                    $newMention[] = $value;
                }
            }

            if (!empty($newMention)) {

                event(new TaskCommentMentionEvent($comment->service job, $comment, $newMention));

            }

        }

    }

}
