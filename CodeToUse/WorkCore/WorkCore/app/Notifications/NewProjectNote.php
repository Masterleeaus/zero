<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Site;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class NewProjectNote extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $site;
    private $emailSetting;
    private $event;

    public function __construct(Site $site, $event)
    {
        $this->site = $site;
        $this->event = $event;
        $this->company = $this->site->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'cleaner-assign-to-site')->first();
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.projectNote.subject'), $this->site->project_name);
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
        $url = route('sites.show', $this->site->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.projectNote.text') . ' - ' . $this->site->project_name . '<br>';

        return parent::build($notifiable)
            ->subject(__('email.projectNote.subject') . ' - ' . config('app.name') . '.')
            ->markdown(
                'mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.projectNote.action'),
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
            'id' => $this->site->id,
            'created_at' => $this->site->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->site->project_name,
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
            ->content('*' . __('email.projectNote.subject') . '*' . "\n" . __('email.projectNote.mentionText') . ' - ' . $this->site->project_name);
    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject(__('email.projectNote.subject'))
            ->body(ucfirst($this->site->project_name) . ' ' . __('email.projectNote.subject'));
    }

}
