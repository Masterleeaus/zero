<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\SlackSetting;
use App\Models\Service Job;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskNoteMention extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service job;
    private $emailSetting;
    private $event;

    public function __construct(Service Job $service job, $event)
    {
        $this->service job = $service job;
        $this->event = $event;
        $this->company = $this->service job->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'service job-mention-notification')->first();

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.taskNote.mentionSubject'), $this->service job->heading);
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
        $url = route('service jobs.show', [$this->service job->id, 'view' => 'notes']);
        $url = getDomainSpecificUrl($url, $this->company);
        $site = ((!is_null($this->service job->site)) ? __('app.site') . ' - ' . $this->service job->site->project_name : '');

        $content = __('email.taskNote.mentionNote') . '<br>' . $site . '<br>';

        return parent::build($notifiable)
            ->subject(__('email.taskNote.mentionSubject') . ' #' . $this->service job->task_short_code . ' - ' . config('app.name') . '.')
            ->markdown(
                'mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.taskNote.action'),
                    'notifiableName' => $notifiable->name
                ]
            );

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
            'created_at' => $this->service job->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->service job->heading,
            'type' => 'note',
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

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.taskNote.mentionNote') . '*' . "\n" . $this->service job->heading . "\n" . ' #' . $this->service job->task_short_code);

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject(__('email.taskNote.mentionSubject'))
            ->body($this->service job->heading . ' ' . __('email.taskNote.subject'));
    }

}
