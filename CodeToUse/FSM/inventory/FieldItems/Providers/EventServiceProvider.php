<?php

namespace Modules\FieldItems\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\FieldItems\Observers\ItemCategoryObserver;
use Modules\FieldItems\Observers\ItemSubCategoryObserver;
use Modules\FieldItems\Observers\ItemFileObserver;
use Modules\FieldItems\Observers\ItemObserver;
use Modules\FieldItems\Entities\ItemCategory;
use Modules\FieldItems\Entities\ItemFiles;
use Modules\FieldItems\Entities\ItemSubCategory;
use Modules\FieldItems\Entities\Item;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $observers = [
        ItemCategory::class => [ItemCategoryObserver::class],
        ItemSubCategory::class => [ItemSubCategoryObserver::class],
        ItemFiles::class => [ItemFileObserver::class],
        Item::class => [ItemObserver::class],
    ];
        if (is_dir($modulePath.'/Resources/lang')) { $this->loadTranslationsFrom($modulePath.'/Resources/lang', 'fielditems'); }
    }