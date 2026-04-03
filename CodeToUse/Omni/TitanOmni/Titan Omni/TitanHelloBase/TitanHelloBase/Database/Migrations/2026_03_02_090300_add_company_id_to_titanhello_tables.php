<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titanhello_call_events') && !Schema::hasColumn('titanhello_call_events', 'company_id')) {
            Schema::table('titanhello_call_events', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_call_notes') && !Schema::hasColumn('titanhello_call_notes', 'company_id')) {
            Schema::table('titanhello_call_notes', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_call_recordings') && !Schema::hasColumn('titanhello_call_recordings', 'company_id')) {
            Schema::table('titanhello_call_recordings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_callback_requests') && !Schema::hasColumn('titanhello_callback_requests', 'company_id')) {
            Schema::table('titanhello_callback_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_calls') && !Schema::hasColumn('titanhello_calls', 'company_id')) {
            Schema::table('titanhello_calls', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_dial_campaign_contacts') && !Schema::hasColumn('titanhello_dial_campaign_contacts', 'company_id')) {
            Schema::table('titanhello_dial_campaign_contacts', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_dial_campaigns') && !Schema::hasColumn('titanhello_dial_campaigns', 'company_id')) {
            Schema::table('titanhello_dial_campaigns', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_inbound_numbers') && !Schema::hasColumn('titanhello_inbound_numbers', 'company_id')) {
            Schema::table('titanhello_inbound_numbers', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_ivr_menus') && !Schema::hasColumn('titanhello_ivr_menus', 'company_id')) {
            Schema::table('titanhello_ivr_menus', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_ivr_options') && !Schema::hasColumn('titanhello_ivr_options', 'company_id')) {
            Schema::table('titanhello_ivr_options', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_ring_group_members') && !Schema::hasColumn('titanhello_ring_group_members', 'company_id')) {
            Schema::table('titanhello_ring_group_members', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanhello_ring_groups') && !Schema::hasColumn('titanhello_ring_groups', 'company_id')) {
            Schema::table('titanhello_ring_groups', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};
