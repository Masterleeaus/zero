<?php

namespace Modules\Timesheet\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Timesheet';
        $menu = $event->menu;

        $menu->add([
            'title' => __('Timesheet::timesheet.menu.title'),
            'icon' => 'clock',
            'name' => 'timesheet',
            'parent' => null,
            'order' => 520,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'timesheet.index',
            'module' => $module,
            'permission' => 'timesheet manage',
        ]);

        $menu->add([
            'title' => __('Timesheet::timesheet.menu.timer'),
            'icon' => 'player-play',
            'name' => 'timesheet_timer',
            'parent' => 'timesheet',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'timesheet.timer.index',
            'module' => $module,
            'permission' => 'timesheet timer',
        ]);

        $menu->add([
            'title' => __('Timesheet::timesheet.menu.approvals'),
            'icon' => 'check',
            'name' => 'timesheet_approvals',
            'parent' => 'timesheet',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'timesheet.approvals.inbox',
            'module' => $module,
            'permission' => 'timesheet approve',
        ]);

        $menu->add([
            'title' => __('Timesheet::timesheet.menu.reports'),
            'icon' => 'chart-bar',
            'name' => 'timesheet_reports',
            'parent' => 'timesheet',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'timesheet.reports.dashboard',
            'module' => $module,
            'permission' => 'timesheet report',
        ]);

        $menu->add([
            'title' => __('Timesheet::timesheet.menu.settings'),
            'icon' => 'settings',
            'name' => 'timesheet_settings',
            'parent' => 'timesheet',
            'order' => 99,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'timesheet.settings.edit',
            'module' => $module,
            'permission' => 'timesheet settings',
        ]);
    }
}
