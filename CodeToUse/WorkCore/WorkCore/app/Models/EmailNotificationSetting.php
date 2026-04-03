<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\EmailNotificationSetting
 *
 * @property int $id
 * @property string $setting_name
 * @property string $send_email
 * @property string $send_slack
 * @property string $send_push
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $slug
 * @property-read mixed $icon
 * @method static Builder|EmailNotificationSetting newModelQuery()
 * @method static Builder|EmailNotificationSetting newQuery()
 * @method static Builder|EmailNotificationSetting query()
 * @method static Builder|EmailNotificationSetting whereCreatedAt($value)
 * @method static Builder|EmailNotificationSetting whereId($value)
 * @method static Builder|EmailNotificationSetting whereSendEmail($value)
 * @method static Builder|EmailNotificationSetting whereSendPush($value)
 * @method static Builder|EmailNotificationSetting whereSendSlack($value)
 * @method static Builder|EmailNotificationSetting whereSettingName($value)
 * @method static Builder|EmailNotificationSetting whereSlug($value)
 * @method static Builder|EmailNotificationSetting whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read Company|null $company
 * @method static Builder|EmailNotificationSetting whereCompanyId($value)
 * @property string $send_twilio
 * @method static Builder|EmailNotificationSetting whereSendTwilio($value)
 * @mixin Eloquent
 */
class EmailNotificationSetting extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

    const NOTIFICATIONS = [
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Expense/Added by Admin',
            'slug' => 'new-expenseadded-by-admin',

        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Expense/Added by Member',
            'slug' => 'new-expenseadded-by-member',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Expense Status Changed',
            'slug' => 'expense-status-changed',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Support Issue / Support Request',
            'slug' => 'new-support-issue / support-request',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Leave Application',
            'slug' => 'new-leave-application',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Service Job Completed',
            'slug' => 'service job-completed',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Service Job Status Changed',
            'slug' => 'service job-status-updated',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Invoice Create/Update Notification',
            'slug' => 'invoice-createupdate-notification',
        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Discussion Reply',
            'slug' => 'discussion-reply',

        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Service / Extra Purchase Request',
            'slug' => 'new-service / extra-purchase-request',

        ],
        [

            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Enquiry notification',
            'slug' => 'enquiry-notification',

        ],
        [

            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Order Create/Update Notification',
            'slug' => 'order-createupdate-notification',

        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Join via Invitation',
            'slug' => 'user-join-via-invitation',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Follow Up Reminder',
            'slug' => 'follow-up-reminder',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Registration/Added by Admin',
            'slug' => 'user-registrationadded-by-admin',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Cleaner Assign to Site',
            'slug' => 'cleaner-assign-to-site',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'New Notice Published',
            'slug' => 'new-notice-published',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'User Assign to Service Job',
            'slug' => 'user-assign-to-service job',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'yes',
            'setting_name' => 'Birthday notification',
            'slug' => 'birthday-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Payment Notification',
            'slug' => 'payment-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Cleaner Appreciation',
            'slug' => 'appreciation-notification',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Holiday Notification',
            'slug' => 'holiday-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Quote Notification',
            'slug' => 'quote-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Event Notification',
            'slug' => 'event-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Team Chat Item Notification',
            'slug' => 'message-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Site Mention Notification',
            'slug' => 'site-mention-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Service Job Mention',
            'slug' => 'service job-mention-notification',
        ],
        [
            'send_email' => 'yes',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Shift Assign Notification',
            'slug' => 'shift-assign-notification',
        ],
        [
            'send_email' => 'no',
            'send_push' => 'no',
            'send_slack' => 'no',
            'setting_name' => 'Daily Schedule Notification',
            'slug' => 'daily-schedule-notification',
        ]
    ];

    public static function userAssignTask()
    {
        return EmailNotificationSetting::where('slug', 'user-assign-to-service job')->first();
    }

}
