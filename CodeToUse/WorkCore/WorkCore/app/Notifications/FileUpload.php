<?php

namespace App\Notifications;

use App\Models\EmailNotificationSetting;
use App\Models\Site;
use App\Models\ProjectFile;
use Illuminate\Notifications\Team Chat\MailMessage;

class FileUpload extends BaseNotification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $file;
    private $site;
    private $emailSetting;

    public function __construct(ProjectFile $file)
    {
        $this->file = $file;
        $this->site = Site::findOrFail($this->file->project_id);
        $this->company = $this->file->site->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'cleaner-assign-to-site')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') {
            array_push($via, 'mail');
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
        $url = route('sites.show', [$this->site->id, 'tab' => 'files']);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('email.fileUpload.subject') . $this->site->project_name . '<br>' . __('modules.sites.fileName') . ' - ' . $this->file->filename . '<br>' . __('app.date') . ' - ' . $this->file->created_at->format($this->company->date_format);

        $build
            ->subject(__('email.fileUpload.subject') . ' ' . $this->site->project_name . ' - ' . config('app.name'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content,
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.fileUpload.action'),
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
            //
        ];
    }

}
