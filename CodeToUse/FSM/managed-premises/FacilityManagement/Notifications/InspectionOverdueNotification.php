<?php

namespace Modules\FacilityManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InspectionOverdueNotification extends Notification
{
    use Queueable;
    public $inspection;
    public function __construct($inspection){ $this->inspection = $inspection; }
    public function via($notifiable){ return ['mail']; }
    public function toMail($notifiable){
        return (new MailMessage)
            ->subject('Inspection Overdue')
            ->line('An inspection is overdue.')
            ->line('Scope: '.$this->inspection->scope_type.' #'.$this->inspection->scope_id)
            ->line('Scheduled at: '.$this->inspection->scheduled_at)
            ->action('View Inspection', url('/inspections/'.$this->inspection->id));
    }
}
