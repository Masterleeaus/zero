@foreach ($enquiries as $enquiry)
    <x-cards.enquiry-card :enquiry="$enquiry" />
@endforeach
