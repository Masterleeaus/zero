<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            if (! Schema::hasColumn('sites', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('territory_id')->index();
            }
            if (! Schema::hasColumn('sites', 'direction')) {
                $table->string('direction')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', static function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('sites', 'direction')) {
                $table->dropColumn('direction');
            }
        });
    }
};
