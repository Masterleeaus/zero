<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        $tables = [
            'sites','buildings','unit_types','units',
            'facility_assets','facility_inspections','facility_docs','facility_meters',
            'facility_meter_reads','facility_occupancies'
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'tenant_id')) {
                Schema::table($t, function(Blueprint $b){ $b->unsignedBigInteger('tenant_id')->nullable()->after('id'); $b->index('tenant_id'); });
            }
        }
    }
    public function down(): void {
        $tables = [
            'sites','buildings','unit_types','units',
            'facility_assets','facility_inspections','facility_docs','facility_meters',
            'facility_meter_reads','facility_occupancies'
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'tenant_id')) {
                Schema::table($t, function(Blueprint $b){ $b->dropIndex([$b->getTable().'_tenant_id_index']); $b->dropColumn('tenant_id'); });
            }
        }
    }
};