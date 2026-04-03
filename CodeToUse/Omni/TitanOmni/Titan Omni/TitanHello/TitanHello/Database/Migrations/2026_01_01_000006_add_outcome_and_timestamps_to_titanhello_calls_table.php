<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_calls', 'call_outcome')) {
                $table->string('call_outcome')->nullable()->after('status'); // answered|missed|failed|voicemail
            }
            if (!Schema::hasColumn('titanhello_calls', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->after('call_outcome');
            }
            if (!Schema::hasColumn('titanhello_calls', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('answered_at');
            }
            if (!Schema::hasColumn('titanhello_calls', 'ring_duration')) {
                $table->integer('ring_duration')->nullable()->after('ended_at');
            }
            if (!Schema::hasColumn('titanhello_calls', 'missed_reason')) {
                $table->string('missed_reason')->nullable()->after('ring_duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            foreach (['missed_reason','ring_duration','ended_at','answered_at','call_outcome'] as $col) {
                if (Schema::hasColumn('titanhello_calls', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
