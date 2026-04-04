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
                'key'   => 'operations_leaves',
                'route' => 'dashboard.work.leaves.index',
                'label' => 'Leave',
                'icon'  => 'tabler-plane-tilt',
                'order' => 52,
            ],
            [
                'key'   => 'money_expenses',
                'route' => 'dashboard.money.expenses.index',
                'label' => 'Expenses',
                'icon'  => 'tabler-report-money',
                'order' => 53,
            ],
            [
                'key'   => 'money_expense_categories',
                'route' => 'dashboard.money.expense-categories.index',
                'label' => 'Expense Categories',
                'icon'  => 'tabler-folders',
                'order' => 54,
            ],
        ];

        foreach ($items as $item) {
            DB::table('menus')->updateOrInsert(
                ['key' => $item['key']],
                [
                    ...$item,
                    'parent_id'      => null,
                    'route_slug'     => null,
                    'svg'            => null,
                    'params'         => json_encode([]),
                    'type'           => 'item',
                    'extension'      => null,
                    'letter_icon'    => false,
                    'letter_icon_bg' => null,
                    'is_active'      => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('menus')->whereIn('key', array_column($items, 'key'))->delete();
    }
};
