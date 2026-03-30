<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_occupancies')) {
            Schema::create('facility_occupancies', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('unit_id');
                $t->string('tenant_type',20)->default('user'); // user|company|department
                $t->unsignedBigInteger('tenant_id')->nullable();
                $t->date('start_date')->nullable();
                $t->date('end_date')->nullable();
                $t->string('status',20)->default('active');
                $t->string('contract_ref',120)->nullable();
                $t->timestamps();
                $t->index(['unit_id','status']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('facility_occupancies'); }
};