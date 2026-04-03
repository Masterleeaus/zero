<?php

namespace Modules\FieldItems\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ItemsViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('fielditems::items.form_pricing_fields', function ($view) {
            $suppliers = [];

            if (Schema::hasTable('suppliers')) {
                $suppliers = DB::table('suppliers')->select('id','name')->orderBy('name')->get();
            }

            $view->with('suppliers', $suppliers);
        });
    }
}
