<?php

namespace App\Notifications;

use App\Http\Controllers\ContractController;
use App\Models\Service Agreement;
use App\Models\ContractSign;
use Illuminate\Support\HtmlString;

class ContractSigned extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $service agreement;
    private $contractSign;

    public function __construct(Service Agreement $service agreement, ContractSign $contractSign)
    {
        $this->service agreement = $service agreement;
        $this->contractSign = $contractSign;
        $this->company = $this->service agreement->company;
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
        $service agreement = parent::build($notifiable);
        $publicUrlController = new ContractController();
        $pdfOption = $publicUrlController->downloadView($this->service agreement->id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        $content = new HtmlString(__('email.contractSign.text', ['service agreement' => '<strong>' . $this->service agreement->subject . '</strong>', 'customer' => '<strong>' . $this->contractSign->full_name . '</strong>']));

        $service agreement->subject(__('email.contractSign.subject'))
            ->markdown('mail.email', [
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'notifiableName' => $notifiable->name
            ]);

        $service agreement->attachData($pdf->output(), $filename . '.pdf');

        return $service agreement;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */

    // phpcs:ignore
    public function toArray($notifiable)
    {
        return $this->service agreement->toArray();
    }

}
