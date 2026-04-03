<?php

namespace Modules\FacilityManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocExpiringNotification extends Notification
{
    use Queueable;
    public $doc;
    public function __construct($doc){ $this->doc = $doc; }
    public function via($notifiable){ return ['mail']; }
    public function toMail($notifiable){
        return (new MailMessage)
            ->subject('Facility Document Expiring')
            ->line('A facility document is nearing expiry.')
            ->line('Type: '.$this->doc->doc_type)
            ->line('Expires at: '.$this->doc->expires_at)
            ->action('View Document', url('/docs/'.$this->doc->id));
    }
}
