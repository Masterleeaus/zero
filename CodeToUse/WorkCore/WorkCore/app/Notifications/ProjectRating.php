<?php

namespace App\Notifications;

use App\Models\Site;
use Illuminate\Notifications\Team Chat\MailMessage;

class ProjectRating extends BaseNotification
{

    private $site;

    /**
     * Create a new notification instance.
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = array('database');

        if ($notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);

        $url = route('sites.show', $this->site->id) . '?tab=rating';
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.projectRating.text') . ' ' . $this->site->project_name;

        $build
            ->subject(__('email.projectRating.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company?->header_color,
                'actionText' => __('email.projectRating.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    //phpcs:ignore
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->site->id,
            'created_at' => $this->site->rating->created_at->format('Y-m-d H:i:s'),
            'heading' => __('email.projectRating.subject'),
        ];
    }

}
