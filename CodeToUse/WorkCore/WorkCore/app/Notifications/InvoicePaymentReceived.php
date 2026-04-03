<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\EmailNotificationSetting;

class InvoicePaymentReceived extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $payment;


    private $emailSetting;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->company = $this->payment->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'payment-notification')->first();

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
        $invoice = Invoice::findOrFail($this->payment->invoice_id);

        if (!is_null($invoice->site) && !is_null($invoice->site->customer) && !is_null($invoice->site->customer->clientDetails)) {

            $customer = $invoice->site->customer;
        }
        elseif (!is_null($invoice->client_id) && !is_null($invoice->clientDetails)) {

            $customer = $invoice->customer;
        }

        if ($invoice->order_id != null) {
            $number = __('app.order') . '#' . $invoice->order_id;
            $message = __('email.invoices.paymentReceivedForOrder');
            $url = route('orders.show', $invoice->order_id);
            $actionBtn = __('email.orders.action');

        }
        else {
            $number = $invoice->invoice_number;
            $message = __('email.invoices.paymentReceivedForInvoice');
            $url = route('invoices.show', $invoice->id);
            $actionBtn = __('email.invoices.action');
        }

        $message .= (isset($customer->name)) ? __('app.by') . ' ' . $customer->name . '.' : '.';

        $url = getDomainSpecificUrl($url, $this->company);

        $content = $message . ':- ' . '<br>' . __('app.invoiceNumber') . ': ' . $number;

        $build
            ->subject(__('email.invoices.paymentReceived') . ' (' . $invoice->invoice_number . ') - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => $actionBtn,
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

        $invoice = Invoice::find($this->payment->invoice_id);

        if ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ];
        }

        return '';
    }

    public function toSlack($notifiable)
    {
        $invoice = Invoice::findOrFail($this->payment->invoice_id);

        return $this->slackBuild($notifiable)
            ->content(__('email.hello') . ' ' . $notifiable->name . "\n" . __('email.invoices.paymentReceivedForInvoice') . ':' . $invoice->invoice_number);

    }

}
