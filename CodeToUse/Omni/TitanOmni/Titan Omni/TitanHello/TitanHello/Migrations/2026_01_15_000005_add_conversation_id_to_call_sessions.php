<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titan_hello_call_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('titan_hello_call_sessions', 'conversation_db_id')) {
                $table->unsignedBigInteger('conversation_db_id')->nullable()->after('agent_id');
                $table->index(['conversation_db_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('titan_hello_call_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('titan_hello_call_sessions', 'conversation_db_id')) {
                $table->dropIndex(['conversation_db_id']);
                $table->dropColumn('conversation_db_id');
            }
        });
    }
};
