<?php

namespace App\Notifications;

use App\Models\Site;

class NewProject extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->company = $this->site->company;
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

        if ($notifiable->email_notifications && $notifiable->email != '') {
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

        $url = route('sites.show', $this->site->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.newProject.text') . ' - ' . ($this->site->project_name) . '<br><br>' . __('email.newProject.loginNow');

        $build
            ->subject(__('email.newProject.subject') . ' - ' . config('app.name') . '.')
            ->greeting(__('email.hello') . ' ' . $notifiable->name . ',')
            ->markdown('mail.site.created', [
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
     * @param mixed $notifiable
     * @return array
     */
    //phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->site->toArray();
    }

}
