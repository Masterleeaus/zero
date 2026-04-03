<?php

namespace Modules\Recruitment\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Recruitment';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Recruitment'),
            'icon' => '',
            'name' => 'recruitment',
            'parent' => 'hrm',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'recruitment manage'
        ]);
        $menu->add([
            'title' => __('Jobs'),
            'icon' => '',
            'name' => 'jobs',
            'parent' => 'recruitment',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.index',
            'module' => $module,
            'permission' => 'job manage'
        ]);
        $menu->add([
            'title' => __('Job Create'),
            'icon' => '',
            'name' => 'job-create',
            'parent' => 'recruitment',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.create',
            'module' => $module,
            'permission' => 'job create'
        ]);
        $menu->add([
            'title' => __('Job Application'),
            'icon' => '',
            'name' => 'job-application',
            'parent' => 'recruitment',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job-application.index',
            'module' => $module,
            'permission' => 'jobapplication manage'
        ]);
        $menu->add([
            'title' => __('Job Candidate'),
            'icon' => '',
            'name' => 'job-candidate',
            'parent' => 'recruitment',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.application.candidate',
            'module' => $module,
            'permission' => 'jobapplication candidate manage'
        ]);
        $menu->add([
            'title' => __('Job On-boarding'),
            'icon' => '',
            'name' => 'job-on-boarding',
            'parent' => 'recruitment',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'job.on.board',
            'module' => $module,
            'permission' => 'jobonboard manage'
        ]);
        $menu->add([
            'title' => __('Custom Question'),
            'icon' => '',
            'name' => 'custom-question',
            'parent' => 'recruitment',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'custom-question.index',
            'module' => $module,
            'permission' => 'custom question manage'
        ]);
        $menu->add([
            'title' => __('Interview Schedule'),
            'icon' => '',
            'name' => 'interview-schedule',
            'parent' => 'recruitment',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'interview-schedule.index',
            'module' => $module,
            'permission' => 'interview schedule manage'
        ]);
        $menu->add([
            'title' => __('Career'),
            'icon' => '',
            'name' => 'career',
            'parent' => 'recruitment',
            'order' => 45,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'career',
            'module' => $module,
            'permission' => 'career manage'
        ]);
    }
}
