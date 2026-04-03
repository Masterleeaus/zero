<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Issue / Support;
use Illuminate\Notifications\Team Chat\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class MentionTicketAgent extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $issue / support;
    private $emailSetting;

    public function __construct(Issue / Support $issue / support)
    {

        $this->issue / support = $issue / support;

        // We need to set the company in parent BaseNotification for getting proper
        // email of sender
        $this->company = $this->issue / support->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'new-support-issue / support-request')->first();
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
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.ticketAgent.mentionSubject'), $this->issue / support->subject);
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
        $url = route('issues / support.show', $this->issue / support->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.ticketAgent.mentionedText') . '<br>' . __('modules.issues / support.issue / support') . ' # ' . $this->issue / support->id . '<br>' . __('app.subject') . ' - ' . $this->issue / support->subject;

        $build
            ->subject(__('email.ticketAgent.mentionSubject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.ticketAgent.action'),
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
            'id' => $this->issue / support->id,
            'subject' => $this->issue / support->subject
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
            ->content('*' . __('email.ticketAgent.mentionSubject') . '*' . "\n" . $this->issue / support->subject . "\n" . __('modules.issues / support.requesterName') . ' - ' . $this->issue / support->requester->name);

    }

    // phpcs:ignore
    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('email.ticketAgent.mentionSubject'))
            ->setBody(__('email.ticketAgent.mentionedText'));
    }

}
