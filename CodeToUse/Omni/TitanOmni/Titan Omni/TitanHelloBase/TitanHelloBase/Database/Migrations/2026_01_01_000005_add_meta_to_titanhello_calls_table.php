<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_calls', 'meta')) {
                $table->json('meta')->nullable()->after('disposition_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (Schema::hasColumn('titanhello_calls', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
