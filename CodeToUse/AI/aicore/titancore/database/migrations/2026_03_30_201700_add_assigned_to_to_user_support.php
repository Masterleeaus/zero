<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_support')) {
            return;
        }

        Schema::table('user_support', static function (Blueprint $table) {
            if (! Schema::hasColumn('user_support', 'assigned_to')) {
                $afterColumn = Schema::hasColumn('user_support', 'company_id') ? 'company_id' : 'user_id';

                $table->unsignedBigInteger('assigned_to')->nullable()->after($afterColumn)->index();

                if (Schema::hasTable('users')) {
                    $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_support')) {
            return;
        }

        Schema::table('user_support', static function (Blueprint $table) {
            if (Schema::hasColumn('user_support', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
        });
    }
};
