<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Site;
use App\Models\ProjectNote;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class ProjectNoteUpdated extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $site;
    private $projectNote;
    private $emailSetting;

    public function __construct(Site $site, ProjectNote $projectNote)
    {
        $this->site = $site;
        $this->projectNote = $projectNote;
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.projectNote.updateSubject'), $this->site->project_name);
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
        $projectNoteUpdate = parent::build($notifiable);

        $url = route('sites.show', $this->site->id) . '?tab=notes';
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.projectNote.updateContent') . '<br>';

        $projectNoteUpdate->subject(__('email.projectNote.updateSubject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.projectNote.action'),
                'notifiableName' => $notifiable->name
            ]);

        return $projectNoteUpdate;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->projectNote->id,
            'project_id' => $this->site->id,
            'project_name' => $this->site->project_name,
            'title' => $this->projectNote->title
        ];
    }

    /**
     * Get the OneSignal representation of the notification.
     *
     * @param mixed $notifiable
     * @return OneSignalMessage
     */
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.projectNote.updateSubject'))
            ->setBody($this->site->project_name . ' - ' . $this->projectNote->title);
    }
}