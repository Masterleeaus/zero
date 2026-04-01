# TitanMemoryService Binding

$this->app->singleton(
    TitanMemoryService::class,
    fn() => new TitanMemoryService()
);
