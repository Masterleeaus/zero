@php
    use App\Models\Quote;if ($notification->data['estimate_number'] == '') {
        $quote = Quote::find($notification->data['id']);
        $estimateNumber = $quote->estimate_number;
    } else {
        $estimateNumber = $notification->data['estimate_number'];
    }
@endphp

<x-cards.notification :notification="$notification" :link="route('quotes.show', $notification->data['id'])"
                      :image="company()->logo_url"
                      :title="__('email.estimateDeclined.subject')" :text="$estimateNumber"
                      :time="$notification->created_at"/>
