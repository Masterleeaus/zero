<?php

namespace App\Notifications;

use App\Models\Quote;
use App\Models\EmailNotificationSetting;

class EstimateAccepted extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $quote;
    private $emailSetting;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
        $this->company = $this->quote->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'quote-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        return $via;
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . $this->quote->estimate_number . ' ' . __('email.estimateAccepted.subject'));

    }

}
