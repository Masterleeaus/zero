<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskComment extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service job;
    private $taskComment;
    private $emailSetting;

    public function __construct(Service Job $service job, $taskComment)
    {
        $this->service job = $service job;
        $this->taskComment = $taskComment;
        $this->emailSetting = EmailNotificationSetting::userAssignTask();
        $this->company = $this->service job->company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {

        if ($notifiable->id == user()->id) {
            return [];
        }

        $via = ['database'];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.taskComment.subject'), $this->service job->heading);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Team Chat\MailMessage
     */
    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);
        $url = route('service jobs.show', [$this->service job->id, 'view' => 'comments']);
        $url = getDomainSpecificUrl($url, $this->company);

        $heading = __('email.taskComment.subject') . ' - ' . $this->service job->heading . ' #' . $this->service job->task_short_code . '<br>';
        $projectName = ($this->service job->site != null) ? '<br>' . __('app.site') . ' - ' . $this->service job->site->project_name . '<br>' : '<br>';
        $comment = '<br>' . __('app.comment') . ' - ' . $this->taskComment->comment . '<br>';
        $commentBy = ($this->taskComment && $this->taskComment->user) ? __('email.taskComment.commentedBy') . ' - ' . $this->taskComment->user->name . '<br>' : '<br>';

        $content = $heading . $projectName . $comment . $commentBy;

        $build
            ->subject(__('email.taskComment.subject') . ' #' . $this->service job->task_short_code . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.taskComment.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
//phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->service job->id,
            'created_at' => $this->taskComment->created_at->format('Y-m-d H:i:s'),
            'user_id' => $this->taskComment->user_id,
            'heading' => $this->service job->heading
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Team Chat\SlackMessage
     */
    public function toSlack($notifiable)
    {

        if ($this->slackUserNameCheck($notifiable)) {
            return $this->slackBuild($notifiable)
                ->content('*' . __('email.taskComment.subject') . '*' . "\n" . $this->service job->heading . "\n" . ' #' . $this->service job->task_short_code);
        }

        return $this->slackRedirectMessage('email.discussionReply.subject', $notifiable);

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.taskComment.subject'))
            ->setBody($this->service job->heading . ' ' . __('email.taskComment.subject'));
    }

}
