<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('facility_docs')) {
            Schema::create('facility_docs', function (Blueprint $t) {
                $t->id();
                $t->string('scope_type',20); // site|building|unit|asset
                $t->unsignedBigInteger('scope_id');
                $t->string('doc_type',50);
                $t->string('path')->nullable();
                $t->date('issued_at')->nullable();
                $t->date('expires_at')->nullable();
                $t->string('status',30)->default('valid');
                $t->timestamps();
                $t->index(['scope_type','scope_id','doc_type']);
            });
        }
    }
    public function down(): void { Schema::dropIfExists('facility_docs'); }
};