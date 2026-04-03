@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.sites'),
    'processRoute' => route('sites.import.process'),
    'backRoute' => route('sites.index'),
    'backButtonText' => __('app.backToProject'),
])
