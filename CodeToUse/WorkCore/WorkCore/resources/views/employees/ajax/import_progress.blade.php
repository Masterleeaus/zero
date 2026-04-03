@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.cleaner'),
    'processRoute' => route('cleaners.import.process'),
    'backRoute' => route('cleaners.index'),
    'backButtonText' => __('app.backToEmployees'),
])
