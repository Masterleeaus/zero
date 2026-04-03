<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_meter_reads')) {
            Schema::create('facility_meter_reads', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('meter_id');
                $t->decimal('reading', 12, 3);
                $t->timestamp('read_at')->nullable();
                $t->unsignedBigInteger('reader_id')->nullable();
                $t->string('source',20)->default('manual');
                $t->timestamps();
                $t->index(['meter_id','read_at']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('facility_meter_reads'); }
};