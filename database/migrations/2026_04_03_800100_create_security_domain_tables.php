<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security Domain — Phase 1 tables
 *
 * cyber_securities        — singleton login-protection config
 * blacklist_ips           — IP address blocklist
 * blacklist_emails        — email / email-domain blocklist
 * login_expiries          — per-user forced-logout expiry dates
 * security_audit_events   — tenant-aware security event audit trail
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cyber_securities')) {
            Schema::create('cyber_securities', static function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('max_retries')->default(3);
                $table->string('email')->nullable();
                $table->unsignedTinyInteger('lockout_time')->default(2)->comment('minutes');
                $table->unsignedTinyInteger('max_lockouts')->default(3);
                $table->unsignedTinyInteger('extended_lockout_time')->default(1)->comment('hours');
                $table->unsignedTinyInteger('reset_retries')->default(24)->comment('hours');
                $table->unsignedTinyInteger('alert_after_lockouts')->default(2);
                $table->unsignedTinyInteger('user_timeout')->default(10)->comment('minutes');
                $table->boolean('ip_check')->default(false);
                $table->string('ip')->nullable();
                $table->boolean('unique_session')->default(false);
                $table->timestamps();
            });

            // Seed the singleton row with safe defaults
            \DB::table('cyber_securities')->insert([
                'max_retries'           => 3,
                'lockout_time'          => 2,
                'max_lockouts'          => 3,
                'extended_lockout_time' => 1,
                'reset_retries'         => 24,
                'alert_after_lockouts'  => 2,
                'user_timeout'          => 10,
                'ip_check'              => false,
                'unique_session'        => false,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        if (! Schema::hasTable('blacklist_ips')) {
            Schema::create('blacklist_ips', static function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('blacklist_emails')) {
            Schema::create('blacklist_emails', static function (Blueprint $table) {
                $table->id();
                $table->string('email', 320)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('login_expiries')) {
            Schema::create('login_expiries', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('expiry_date');
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('security_audit_events')) {
            Schema::create('security_audit_events', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('event_type', 60)->index();
                $table->string('ip_address', 45)->nullable();
                $table->string('email', 320)->nullable();
                $table->json('context')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_events');
        Schema::dropIfExists('login_expiries');
        Schema::dropIfExists('blacklist_emails');
        Schema::dropIfExists('blacklist_ips');
        Schema::dropIfExists('cyber_securities');
    }
};
