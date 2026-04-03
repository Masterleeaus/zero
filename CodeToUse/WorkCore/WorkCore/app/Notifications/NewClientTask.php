<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;
use Illuminate\Notifications\Team Chat\MailMessage;

class NewClientTask extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service job;
    private $user;
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
        $startDate = (!is_null($this->service job->start_date)) ? $this->service job->start_date->format($this->company->date_format) : null;

        $content = __('email.newClientTask.content') . ': <b style="color: black"> '. $this->service job->site->project_name . '</b><p>'
        .__('app.service job'). ' '. __('app.details'). ':' .'<br>' .
        ' <b style="color: green">' . __('app.service job') . __('app.name') . ': ' . $this->service job->heading . '</b> <br> ' .
           ' <b style="color: green">' . __('app.startDate') . ': ' . $startDate . '</b>
        </p>';
        $build
            ->subject(__('email.newClientTask.subject') . ' ' . $this->service job->heading . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.service job.service job-created-customer-notification', [
                'content' => $content,
                'notifiableName' => $notifiable->name,
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

}
