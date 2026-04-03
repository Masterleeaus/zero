<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_calls', 'last_event_at')) {
                $table->dateTime('last_event_at')->nullable()->after('ended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (Schema::hasColumn('titanhello_calls', 'last_event_at')) $table->dropColumn('last_event_at');
        });
    }
};
