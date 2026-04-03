<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_calls', 'assigned_at')) {
                $table->dateTime('assigned_at')->nullable()->after('assigned_to_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (Schema::hasColumn('titanhello_calls', 'assigned_at')) $table->dropColumn('assigned_at');
        });
    }
};
