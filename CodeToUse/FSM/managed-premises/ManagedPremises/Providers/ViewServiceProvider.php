<?php
namespace Modules\ManagedPremises\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Modules\ManagedPremises\Http\View\Composers\PropertyWidgetsComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Attach widgets to properties index (module landing page)
        View::composer('managedpremises::properties.index', PropertyWidgetsComposer::class);
    }
}
