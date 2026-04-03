<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskReminder extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service job;
    private $emailSetting;

    public function __construct(Service Job $service job)
    {

        $this->service job = $service job;
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
        $via = array();

        if ($notifiable->email_notifications && $notifiable->email != '') {
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.reminder.subject'), $this->service job->heading);
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
        $url = route('front.task_detail', [$this->service job->hash]);

        $url = getDomainSpecificUrl($url, $this->company);

        $content = $this->service job->heading . ' #' . $this->service job->task_short_code . '<p>';

        if ($this->service job->due_date) {
            $content .= '<b style="color: green">' . __('app.dueDate') . ' : ' . $this->service job->due_date->format($this->company->date_format) . '</b>
            </p>';
        }

        $build
            ->subject(__('email.reminder.subject') . ' #' . $this->service job->task_short_code . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.service job.reminder', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color
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
            'created_at' => $this->service job->created_at->format('Y-m-d H:i:s'),
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

        $dueDate = (!is_null($this->service job->due_date)) ? $this->service job->due_date->format($this->company->date_format) : null;

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.reminder.subject') . '*' . "\n" . $this->service job->heading . "\n" . ' #' . $this->service job->task_short_code . "\n" . __('app.dueDate') . ': ' . $dueDate);

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.reminder.subject'))
            ->setBody($this->service job->heading . ' #' . $this->service job->task_short_code);
    }

}
