<?php
namespace Modules\PropertyManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Modules\PropertyManagement\Http\View\Composers\PropertyWidgetsComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Attach widgets to properties index (module landing page)
        View::composer('propertymanagement::properties.index', PropertyWidgetsComposer::class);
    }
}
