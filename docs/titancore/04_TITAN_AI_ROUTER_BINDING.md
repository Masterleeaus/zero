# TitanAIRouter Binding

Bind singleton:

$this->app->singleton(
    TitanAIRouter::class,
    fn() => new TitanAIRouter(config('titan.ai'))
);
