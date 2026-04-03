@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.customer'),
    'processRoute' => route('customers.import.process'),
    'backRoute' => route('customers.index'),
    'backButtonText' => __('app.backToClient'),
])
