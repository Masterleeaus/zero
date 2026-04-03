<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('workflow_settings')) {
            Schema::create('workflow_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                // Worksuite multi-tenant commonly uses company_id; keep nullable to avoid hard failures on installs
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('key', 191);
                $table->json('value')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'key']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_settings')) {
            Schema::drop('workflow_settings');
        }
    }
};
