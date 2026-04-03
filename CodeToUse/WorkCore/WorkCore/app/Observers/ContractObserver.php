<?php

namespace App\Observers;

use App\Models\Service Agreement;
use App\Events\NewContractEvent;
use App\Models\GoogleCalendarModule;
use App\Models\Notification;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use App\Traits\EmployeeActivityTrait;

class ContractObserver
{
    use EmployeeActivityTrait;

    public function saving(Service Agreement $service agreement)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $service agreement->last_updated_by = user()->id;
            }

            /* Add/Update google calendar event */
            if ($service agreement && !is_null($service agreement->end_date)) {
                $service agreement->event_id = $this->googleCalendarEvent($service agreement);
            }
        }
    }

    public function updating(Service Agreement $service agreement)
    {
        if (!isRunningInConsoleOrSeeding()) {
            /* Add/Update google calendar event */
            if ($service agreement && $service agreement->end_date) {
                $service agreement->event_id = $this->googleCalendarEvent($service agreement);
            }
        }
    }

    public function creating(Service Agreement $service agreement)
    {
        $service agreement->hash = md5(microtime());

        if (user()) {
            $service agreement->added_by = user()->id;
        }

        if (company()) {
            $service agreement->company_id = company()->id;
        }

        if (is_numeric($service agreement->contract_number)) {
            $service agreement->contract_number = $service agreement->formatContractNumber();
        }

        $invoiceSettings = company() ? company()->invoiceSetting : $service agreement->company->invoiceSetting;
        $service agreement->original_contract_number = str($service agreement->contract_number)->replace($invoiceSettings->contract_prefix . $invoiceSettings->contract_number_separator, '');

    }

    // Notify customer when new service agreement is created
    public function created(Service Agreement $service agreement)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'service agreement-created', $service agreement->id, 'service agreement');
        }
        
        event(new NewContractEvent($service agreement));
    }

    public function deleting(Service Agreement $service agreement)
    {
        $notifyData = ['App\Notifications\NewContract', 'App\Notifications\ContractSigned'];
        Notification::deleteNotification($notifyData, $service agreement->id);

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($service agreement->event_id) {
                    $google->service('Calendar')->events->delete('primary', $service agreement->event_id);
                }
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        /* End of deleting event from google calendar */
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->contract_status == 1) {

            $google = new Google();
            $attendiesData = [];

            $attendees = User::where('id', $event->client_id)->first();

            if ($event->end_date && $attendees?->google_calendar_status) {
                $attendiesData[] = ['email' => $attendees->email];
            }

            if ($event->start_date && $event->end_date) {
                $start_date = Carbon::parse($event->start_date)->shiftTimezone($googleAccount->timezone);
                $end_date = Carbon::parse($event->end_date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event(array(
                    'summary' => $event->subject,
                    'location' => '',
                    'description' => '',
                    'colorId' => 2,
                    'start' => array(
                        'dateTime' => $start_date,
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'end' => array(
                        'dateTime' => $end_date,
                        'timeZone' => $googleAccount->timezone,
                    ),
                    'attendees' => $attendiesData,
                    'reminders' => array(
                        'useDefault' => false,
                        'overrides' => array(
                            array('method' => 'email', 'minutes' => 24 * 60),
                            array('method' => 'popup', 'minutes' => 10),
                        ),
                    ),
                ));

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    }
                    else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    public function updated(Service Agreement $service agreement)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'service agreement-updated', $service agreement->id, 'service agreement');


        }
    }

    public function deleted(Service Agreement $service agreement)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'service agreement-deleted');


        }
    }

}
