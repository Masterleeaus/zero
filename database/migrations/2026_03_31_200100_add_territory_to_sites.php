<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            if (! Schema::hasColumn('sites', 'territory_id')) {
                $table->unsignedBigInteger('territory_id')->nullable()->after('team_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'territory_id')) {
                $table->dropColumn('territory_id');
            }
        });
    }
};
