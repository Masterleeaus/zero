<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('quote_id')->nullable()->index()->after('status');
            $table->timestamp('follow_up_at')->nullable()->after('quote_id');
            $table->text('follow_up_note')->nullable()->after('follow_up_at');
            $table->boolean('follow_up_done')->default(false)->after('follow_up_note');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('enquiry_id')->nullable()->index()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('enquiry_id');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['quote_id', 'follow_up_at', 'follow_up_note', 'follow_up_done']);
        });
    }
};
