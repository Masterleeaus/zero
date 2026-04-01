# Signal Subscription Template

Example:

Signal::listen('quote.accepted', function ($payload) {

    Pulse::run('schedule_followup', $payload);

});
