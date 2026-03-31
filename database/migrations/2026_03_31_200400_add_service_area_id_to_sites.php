<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            $table->unsignedBigInteger('service_area_id')->nullable()->after('team_id')->index();

            $table->foreign('service_area_id')
                ->references('id')
                ->on('service_areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            $table->dropForeign(['service_area_id']);
            $table->dropColumn('service_area_id');
        });
    }
};
