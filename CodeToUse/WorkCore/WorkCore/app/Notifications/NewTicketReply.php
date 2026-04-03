<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\TicketReply;
use Illuminate\Support\HtmlString;

class NewTicketReply extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $issue / support;
    private $ticketReply;
    private $emailSetting;

    public function __construct(TicketReply $issue / support)
    {
        $this->ticketReply = $issue / support;
        $this->issue / support = $issue / support->issue / support;
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

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active' && $notifiable->isEmployee($notifiable->id)) {
            array_push($via, 'slack');
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        $build = parent::build($notifiable);

        $url = route('issues / support.show', $this->issue / support->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);

        if ($this->ticketReply->user_id == $notifiable->id) {
            $text = '<p>' . __('email.ticketReply.repliedText') . $this->issue / support->subject . ' #' . $this->issue / support->ticket_number . '</p>' . __('app.by') . ' ' . $this->ticketReply->user->name;
        }
        else {
            $text = '<p>' . __('email.ticketReply.receivedText') . $this->issue / support->subject . ' #' . $this->issue / support->ticket_number . '</p>' . __('app.by') . ' ' . $this->ticketReply->user->name;
        }

        $content = new HtmlString($text);

        $build
            ->subject(__('email.ticketReply.subject') . ' - ' . $this->issue / support->subject)
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.ticketReply.action'),
                'notifiableName' => $notifiable->name
            ]);

        parent::resetLocale();

        return $build;
    }

    public function toSlack($notifiable)
    {

        $url = route('issues / support.show', $this->issue / support->ticket_number);
        $url = getDomainSpecificUrl($url, $this->company);

        return $this->slackBuild($notifiable)
            ->content('*' . __('email.ticketReply.subject') . '*' . "\n" . $this->issue / support->subject . "\n" . __('modules.issues / support.requesterName') . ' - ' . $this->issue / support->requester->name . "\n" . '<' . $url . '|' . __('modules.issues / support.issue / support') . ' #' . $this->issue / support->id . '>' . "\n");

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
            'created_at' => $this->ticketReply->created_at->format('Y-m-d H:i:s'),
            'subject' => $this->issue / support->subject,
            'user_id' => $this->ticketReply->user_id,
            'status' => $this->issue / support->status,
            'agent_id' => $this->issue / support->agent_id,
            'ticket_number' => $this->issue / support->ticket_number
        ];
    }

}
