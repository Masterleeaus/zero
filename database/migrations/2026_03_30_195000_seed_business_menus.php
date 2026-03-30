<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $items = [
            [
                'key'        => 'crm_customers',
                'route'      => 'dashboard.crm.customers.index',
                'label'      => 'Customers',
                'icon'       => 'tabler-users',
                'order'      => 40,
            ],
            [
                'key'        => 'work_sites',
                'route'      => 'dashboard.work.sites.index',
                'label'      => 'Sites',
                'icon'       => 'tabler-building',
                'order'      => 41,
            ],
            [
                'key'        => 'work_service_jobs',
                'route'      => 'dashboard.work.service-jobs.index',
                'label'      => 'Service Jobs',
                'icon'       => 'tabler-briefcase',
                'order'      => 42,
            ],
            [
                'key'        => 'money_quotes',
                'route'      => 'dashboard.money.quotes.index',
                'label'      => 'Quotes',
                'icon'       => 'tabler-file-dollar',
                'order'      => 43,
            ],
            [
                'key'        => 'money_invoices',
                'route'      => 'dashboard.money.invoices.index',
                'label'      => 'Invoices',
                'icon'       => 'tabler-file-invoice',
                'order'      => 44,
            ],
            [
                'key'        => 'insights_overview',
                'route'      => 'dashboard.insights.overview',
                'label'      => 'Insights',
                'icon'       => 'tabler-chart-bar',
                'order'      => 45,
            ],
            [
                'key'        => 'work_attendance',
                'route'      => 'dashboard.work.attendance.index',
                'label'      => 'Attendance',
                'icon'       => 'tabler-calendar-check',
                'order'      => 46,
            ],
            [
                'key'        => 'work_shifts',
                'route'      => 'dashboard.work.shifts.index',
                'label'      => 'Shifts',
                'icon'       => 'tabler-calendar-event',
                'order'      => 47,
            ],
            [
                'key'        => 'work_agreements',
                'route'      => 'dashboard.work.agreements.index',
                'label'      => 'Service Agreements',
                'icon'       => 'tabler-file-certificate',
                'order'      => 48,
            ],
            [
                'key'        => 'work_timelogs',
                'route'      => 'dashboard.work.timelogs.index',
                'label'      => 'Timelogs',
                'icon'       => 'tabler-timeline',
                'order'      => 49,
            ],
            [
                'key'        => 'support_requests',
                'route'      => 'dashboard.support.index',
                'label'      => 'Support',
                'icon'       => 'tabler-lifebuoy',
                'order'      => 50,
            ],
            [
                'key'        => 'user_notifications',
                'route'      => 'dashboard.user.notifications.index',
                'label'      => 'Notifications',
                'icon'       => 'tabler-bell',
                'order'      => 51,
            ],
        ];

        foreach ($items as $item) {
            DB::table('menus')->updateOrInsert(
                ['key' => $item['key']],
                [
                    ...$item,
                    'parent_id' => null,
                    'route_slug'=> null,
                    'svg'       => null,
                    'params'    => json_encode([]),
                    'type'      => 'item',
                    'extension' => null,
                    'letter_icon' => false,
                    'letter_icon_bg' => null,
                    'is_active' => true,
                    'created_at'=> $now,
                    'updated_at'=> $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('menus')->whereIn('key', [
            'crm_customers',
            'work_sites',
            'work_service_jobs',
            'money_quotes',
            'money_invoices',
            'insights_overview',
            'work_attendance',
            'work_shifts',
            'work_agreements',
            'work_timelogs',
            'support_requests',
            'user_notifications',
        ])->delete();
    }
};
