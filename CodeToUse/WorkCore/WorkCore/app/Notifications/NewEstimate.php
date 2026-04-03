<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Quote;
use App\Models\GlobalSetting;
use Illuminate\Notifications\Team Chat\MailMessage;

class NewEstimate extends BaseNotification
{


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $quote;
    private $user;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
        $this->user = User::findOrFail($quote->client_id);
        $this->company = $this->quote->company;
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

        if (push_setting()->beams_push_status == 'active') {
            $pushNotification = new \App\Http\Controllers\DashboardController();
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.quote.subject'), $this->quote->estimate_number);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Team Chat\MailMessage
     */
    // phpcs:ignore
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = url()->temporarySignedRoute('front.quote.show', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->quote->hash);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.quote.text') . '<br>' . __('app.menu.quote') . ' ' . __('app.number') . ': ' .$this->quote->estimate_number ;

        $build
            ->subject(__('email.quote.subject') . ' (' . $this->quote->estimate_number . ') - ' . config('app.name') . '.')
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimateDeclined.action'),
                'notifiableName' => $this->user->name
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
            'id' => $this->quote->id,
            'estimate_number' => $this->quote->estimate_number
        ];
    }

}
