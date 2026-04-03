<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_inspections')) {
            Schema::create('facility_inspections', function (Blueprint $t) {
                $t->id();
                $t->string('scope_type',20); // site|building|unit|asset
                $t->unsignedBigInteger('scope_id');
                $t->json('checklist_json')->nullable();
                $t->unsignedBigInteger('inspector_id')->nullable();
                $t->string('status',30)->default('scheduled');
                $t->timestamp('scheduled_at')->nullable();
                $t->timestamp('completed_at')->nullable();
                $t->json('result_json')->nullable();
                $t->timestamps();
                $t->index(['scope_type','scope_id']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('facility_inspections'); }
};