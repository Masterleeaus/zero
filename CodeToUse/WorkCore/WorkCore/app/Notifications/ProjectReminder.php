<?php

namespace App\Notifications;

use Illuminate\Notifications\Team Chat\MailMessage;
use Illuminate\Support\HtmlString;

class ProjectReminder extends BaseNotification
{


    private $sites;
    private $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($sites, $data)
    {
        $this->sites = $sites;
        $this->data = $data;

        if (isset($this->sites[0])) {
            $this->company = $this->sites[0]->company;
        }

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

        $url = route('sites.index');
        $url = getDomainSpecificUrl($url, $this->company);

        $list = $this->projectList();
        $content = __('email.projectReminder.text') . ' ' . now($this->data['company']->timezone)->addDays($this->data['project_setting']->remind_time)->toFormattedDateString() . '<br>' . new HtmlString($list) . '<br>' . __('email.team chat.loginForMoreDetails');

        $build
            ->subject(__('email.projectReminder.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company?->header_color,
                'actionText' => __('email.projectReminder.action'),
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
        return $this->sites->toArray();
    }

    private function projectList()
    {
        $list = '<ol>';

        foreach ($this->sites as $site) {
            $list .= '<li><strong>' . $site->project_short_code . '</strong> ' . $site->project_name . '</li>';
        }

        $list .= '</ol>';

        return $list;
    }

}
