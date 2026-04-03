<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Service Job;

class TaskUpdatedClient extends BaseNotification
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
        $url = route('service jobs.show', $this->service job->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = $this->service job->heading . ' ' . __('email.taskUpdate.subject') . '.';

        $build
            ->subject(__('email.taskUpdate.subject') . ' - ' . config('app.name') . '.')
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
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->service job->id,
            'updated_at' => $this->service job->updated_at->format('Y-m-d H:i:s'),
            'created_at' => $this->service job->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->service job->heading
        ];
    }

}
