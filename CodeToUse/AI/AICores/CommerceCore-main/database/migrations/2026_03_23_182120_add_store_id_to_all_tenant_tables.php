<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'abandoned_carts', 'accounts', 'action_logs', 'attendances', 
        'branch_inventories', 'builder_contents', 'builder_sections', 'combo_items', 
        'courier_payments', 'couriers', 'inventory_transfers', 'journal_lines', 
        'leave_requests', 'loyalty_points', 'notification_logs', 'order_items', 
        'payrolls', 'product_batches', 'product_reviews', 
        'product_variants', 'purchase_items', 'sale_return_items', 'shipments', 
        'zone_inventories'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'store_id')) {
                    $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_tenant_tables', function (Blueprint $table) {
            //
        });
    }
};
