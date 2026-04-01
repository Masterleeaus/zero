<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('lifecycle_status')->default('NEW')->after('status');
            $table->foreignId('assigned_agent_id')->nullable()->after('lifecycle_status')->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable()->after('assigned_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->dropColumn(['lifecycle_status', 'assigned_agent_id', 'locked_at']);
        });
    }
};
