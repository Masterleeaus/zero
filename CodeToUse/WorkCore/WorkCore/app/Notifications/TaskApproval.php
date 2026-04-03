<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;
use App\Models\User;
use Illuminate\Notifications\Team Chat\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskApproval extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service job;
    private $updatedBy;
    /**
     * @var mixed
     */
    private $emailSetting;

    public function __construct(Service Job $service job, User $updatedBy = null)
    {
        $this->service job = $service job;
        $this->updatedBy = $updatedBy;
        $this->company = $this->service job->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)
                                ->where('slug', 'service job-status-updated')->first();

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

        if ($this->emailSetting->send_email == 'yes' && isset($notifiable) && $notifiable->email_notifications && $notifiable->email != '') {
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.taskApproval.subject'), $this->service job->heading);
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
        $build = parent::build();
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $projectTitle = (!is_null($this->service job->site)) ? __('app.site') . ' - ' . $this->service job->site->project_name : '';

        $content = __('email.taskApproval.text') . '<br><br>' . 'Service Job Status: ' . $this->service job->boardColumn->column_name . '<br>' . __('email.taskApproval.updatedBy') . ': ' . $this->updatedBy->name . '<br>' . __('app.service job') . ': ' . $this->service job->heading . '<br>' . $projectTitle;

        $build
            ->subject(__('email.taskApproval.subject') . ' #' . $this->service job->task_short_code . ' - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.taskApproval.action'),
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
            'created_at' => $this->service job->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->service job->heading,
            'completed_on' => (!is_null($this->service job->completed_on)) ? $this->service job->completed_on->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
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
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.taskApproval.subject') . '*' . "\n" . '<' . $url . '|' . $this->service job->heading . '>' . "\n" . ' #' . $this->service job->task_short_code . (!is_null($this->service job->site) ? "\n" . __('app.site') . ' - ' . $this->service job->site->project_name : ''));

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.taskApproval.subject'))
            ->setBody($this->service job->heading . ' ' . __('email.taskApproval.subject') . ' #' . $this->service job->task_short_code);
    }

}
