<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Enquiry;
use App\Models\Deal;

class NewLeadCreated extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $leadContact;
    private $emailSetting;

    public function __construct(Enquiry $leadContact)
    {
        $this->leadContact = $leadContact;
        $this->company = $this->leadContact->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'enquiry-notification')->first();
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

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.enquiry.subject'), $this->leadContact->client_name);
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
        $url = route('enquiry-contact.show', $this->leadContact->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $leadEmail = __('modules.enquiry.clientEmail') . ': ';
        $clientEmail = !is_null($this->leadContact->client_email) ? $leadEmail . $this->leadContact->client_email . '<br>' : '';
        $content = __('email.enquiry.subject') . '<br>' . __('modules.enquiry.clientName') . ': '  . $this->leadContact->client_name_salutation . '<br>' . $clientEmail;

        if (session()->has('deal_name')) {
            $content .=  __('modules.deal.dealName') . ": " . session('deal_name') . '<br>';
        }

        $build
            ->subject(__('email.enquiry.subject') . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.enquiry.action'),
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
            'id' => $this->leadContact->id,
            'name' => $this->leadContact->client_name,
            'agent_id' => $notifiable->id,
            'added_by' => $this->leadContact->added_by
        ];
    }

}
