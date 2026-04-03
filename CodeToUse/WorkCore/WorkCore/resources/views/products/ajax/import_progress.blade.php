@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.service / extra'),
    'processRoute' => route('services / extras.import.process'),
    'backRoute' => route('services / extras.index'),
    'backButtonText' => __('app.backToProducts'),
])
