<?php

namespace Modules\FormBuilder\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'FormBuilder';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Form Builder'),
            'icon' => '',
            'name' => 'formbuilder',
            'parent' => 'crm',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'form_builder.index',
            'module' => $module,
            'permission' => 'formbuilder manage'
        ]);
    }
}
