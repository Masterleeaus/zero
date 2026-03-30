<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_assets')) {
            Schema::create('facility_assets', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('site_id')->nullable();
                $t->unsignedBigInteger('building_id')->nullable();
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->string('asset_type',100);
                $t->string('label',150);
                $t->string('serial_no',150)->nullable();
                $t->string('status',50)->default('active');
                $t->date('installed_at')->nullable();
                $t->date('next_service_at')->nullable();
                $t->json('meta')->nullable();
                $t->timestamps();
                $t->index(['site_id','building_id','unit_id']);
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('facility_assets');
    }
};