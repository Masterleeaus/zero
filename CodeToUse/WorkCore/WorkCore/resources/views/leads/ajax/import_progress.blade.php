@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.enquiry'),
    'processRoute' => route('enquiry-contact.import.process'),
    'backRoute' => route('enquiry-contact.index'),
    'backButtonText' => __('app.backToLead'),
])
