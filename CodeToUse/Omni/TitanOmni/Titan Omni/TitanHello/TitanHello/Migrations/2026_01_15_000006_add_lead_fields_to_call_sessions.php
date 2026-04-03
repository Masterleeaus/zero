<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titan_hello_call_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('titan_hello_call_sessions', 'caller_name')) {
                $table->string('caller_name', 128)->nullable()->after('from_number');
            }
            if (!Schema::hasColumn('titan_hello_call_sessions', 'job_type')) {
                $table->string('job_type', 128)->nullable()->after('caller_name');
            }
            if (!Schema::hasColumn('titan_hello_call_sessions', 'suburb')) {
                $table->string('suburb', 128)->nullable()->after('job_type');
            }
            if (!Schema::hasColumn('titan_hello_call_sessions', 'urgency')) {
                $table->string('urgency', 32)->nullable()->after('suburb');
            }
            if (!Schema::hasColumn('titan_hello_call_sessions', 'callback_window')) {
                $table->string('callback_window', 64)->nullable()->after('urgency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titan_hello_call_sessions', function (Blueprint $table) {
            foreach (['callback_window','urgency','suburb','job_type','caller_name'] as $col) {
                if (Schema::hasColumn('titan_hello_call_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
