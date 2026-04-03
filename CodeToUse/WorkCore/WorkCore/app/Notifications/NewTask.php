<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use App\Models\Service Job;
use Illuminate\Notifications\Team Chat\MailMessage;
use Illuminate\Support\Facades\App;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewTask extends BaseNotification
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.newTask.subject'), $this->service job->heading);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $dueDate = (!is_null($this->service job->due_date)) ? $this->service job->due_date->format($this->company->date_format) : null;
        $taskShortCode = (!is_null($this->service job->task_short_code)) ? '#' . $this->service job->task_short_code . ' - ' : ' ';


        $content = $this->service job->heading . ' ' . $taskShortCode . '<p>
            <b style="color: green">' . __('app.dueDate') . ': ' . $dueDate . '</b>
        </p>';

        $subject = __('email.newTask.subject') . ' ' . $taskShortCode . config('app.name'). '.';

        $build
            ->subject($subject)
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.service job.created', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
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
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);
        $taskShortCode = $this->service job->task_short_code ? ' #' . $this->service job->task_short_code : '';

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.newTask.subject') . '*' . "\n" . '<' . $url . '|' . $this->service job->heading . '>' . "\n" . $taskShortCode . "\n" . __('app.dueDate') . ': ' . $dueDate . (!is_null($this->service job->site) ? "\n" . __('app.site') . ' - ' . $this->service job->site->project_name : ''));

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.newTask.subject'))
            ->setBody($this->service job->heading);
    }

}
