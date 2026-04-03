<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use Illuminate\Support\Facades\Session;

class LeadImported extends BaseNotification
{
/**
     * Create a new notification instance.
     *
     * @return void
     */
    private $emailSetting;

    public function __construct()
    {
        $this->emailSetting = EmailNotificationSetting::where('company_id', company()->id)->where('slug', 'enquiry-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = array('database');

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

        $enquiries = Session::get('enquiries', []);

        $content = __('email.enquiries.subject') . '<br>';

        $counter = 0;
        foreach ($enquiries as $enquiry) {
            $counter++;

            if (!empty($enquiry['lead_name'])) {
                $content .= __('modules.enquiry.clientName') . ": " . nl2br($enquiry['lead_name']) . "<br>";
            }

            if (!empty($enquiry['email'])) {
                $content .= __('modules.enquiry.clientEmail') . ": " . $enquiry['email'] . "<br>";
            }

            if (!empty($enquiry['deal_name'])) {
                $content .= __('modules.deal.dealName') . ": " . nl2br($enquiry['deal_name']) . "<br>";
            }

            if ($counter >= 10) {
                break;
            }
        }

        $content .= "<br>";

        if (count($enquiries) > 10) {
            $url = route('enquiry-contact.index');
            $build
                ->subject(__('email.enquiries.subject') . ' - ' . config('app.name'))
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => company()->header_color,
                    'actionText' => __('email.leadAgent.viewMore'),
                    'notifiableName' => $notifiable->name
                ]);
        } else {
            $build
                ->subject(__('email.enquiries.subject') . ' - ' . config('app.name'))
                ->markdown('mail.email', [
                    'content' => $content,
                    'themeColor' => company()->header_color,
                    'notifiableName' => $notifiable->name
                ]);
        }
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
        $enquiries = Session::get('enquiries', []);

        return [
            'enquiries' => $enquiries
        ];
    }
}
