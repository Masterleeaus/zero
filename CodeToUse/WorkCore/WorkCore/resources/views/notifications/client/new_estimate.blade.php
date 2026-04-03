@php
if ($notification->data['estimate_number'] == '') {
    $quote = \App\Models\Quote::find($notification->data['id']);
    $estimateNumber = $quote->estimate_number;
} else {
    $estimateNumber = $notification->data['estimate_number'];
}
@endphp

<x-cards.notification :notification="$notification"  :link="route('quotes.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.quote.subject')" :text="$estimateNumber" :time="$notification->created_at" />
