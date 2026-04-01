<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tz_process_dependencies')) {
            return;
        }
        Schema::create('tz_process_dependencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->string('parent_process_id', 80);
            $table->string('child_process_id', 80);
            $table->string('relationship_type', 50)->default('cascade');
            $table->timestamps();
            $table->index(['company_id', 'parent_process_id'], 'idx_tz_proc_dep_parent');
            $table->index(['company_id', 'child_process_id'], 'idx_tz_proc_dep_child');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tz_process_dependencies');
    }
};
