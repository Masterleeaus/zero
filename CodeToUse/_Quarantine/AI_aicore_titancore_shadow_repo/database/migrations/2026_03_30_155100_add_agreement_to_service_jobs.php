<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'agreement_id')) {
                $table->unsignedBigInteger('agreement_id')->nullable()->after('quote_id');
                $table->index(['company_id', 'agreement_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'agreement_id')) {
                $table->dropColumn('agreement_id');
            }
        });
    }
};
