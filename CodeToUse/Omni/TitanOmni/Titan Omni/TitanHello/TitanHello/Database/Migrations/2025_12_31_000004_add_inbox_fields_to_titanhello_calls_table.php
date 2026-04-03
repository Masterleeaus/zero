<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_calls', 'priority')) {
                $table->unsignedTinyInteger('priority')->default(0)->after('disposition_notes');
            }
            if (!Schema::hasColumn('titanhello_calls', 'callback_due_at')) {
                $table->dateTime('callback_due_at')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('titanhello_calls', 'missed_at')) {
                $table->dateTime('missed_at')->nullable()->after('callback_due_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (Schema::hasColumn('titanhello_calls', 'missed_at')) $table->dropColumn('missed_at');
            if (Schema::hasColumn('titanhello_calls', 'callback_due_at')) $table->dropColumn('callback_due_at');
            if (Schema::hasColumn('titanhello_calls', 'priority')) $table->dropColumn('priority');
        });
    }
};
