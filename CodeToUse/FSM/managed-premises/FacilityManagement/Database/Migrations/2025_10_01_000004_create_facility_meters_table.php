<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_meters')) {
            Schema::create('facility_meters', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->unsignedBigInteger('asset_id')->nullable();
                $t->string('meter_type',30); // water|power|gas
                $t->string('barcode',120)->nullable();
                $t->decimal('last_reading', 12, 3)->nullable();
                $t->timestamp('last_read_at')->nullable();
                $t->timestamps();
                $t->index(['unit_id','asset_id','meter_type']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('facility_meters'); }
};