<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;
use Illuminate\Notifications\Team Chat\MailMessage;

class TaskUpdated extends BaseNotification
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
        $this->company = $this->service job->load('company')->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'user-assign-to-service job')->first();
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
        $taskShortCode = (!is_null($this->service job->task_short_code)) ? '#' . $this->service job->task_short_code : ' ';

        $content = __('email.taskUpdate.text') . ' <br><br>' . $this->service job->heading . ' - ' . $taskShortCode . ' <br><br>' . __('email.taskUpdate.text2');
        $subject = __('email.taskUpdate.subject') . ' - ' . $taskShortCode . ' - ' . config('app.name'). '.';


        $build
            ->subject($subject)
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.taskUpdate.action'),
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
            'updated_at' => $this->service job->updated_at->format('Y-m-d H:i:s'),
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

        $labels = '';
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);

        foreach ($this->service job->labels as $key => $label) {
            if ($key == 0) {
                $labels .= __('app.label') . ' - ';
            }

            $labels .= $label->label_name;

            if ($key + 1 != count($this->service job->labels)) {
                $labels .= ', ';
            }
        }

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.taskUpdate.subject') . '*' . "\n" . '<' . $url . '|' . $this->service job->heading . '>' . "\n" . ' #' . $this->service job->task_short_code . (!is_null($this->service job->site) ? "\n" . __('app.site') . ' - ' . $this->service job->site->project_name : '') . "\n" . $labels);


    }

}
