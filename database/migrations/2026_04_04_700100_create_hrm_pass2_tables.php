<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── departments ────────────────────────────────────────────────────
        Schema::create('departments', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // ── shift_assignments ──────────────────────────────────────────────
        Schema::create('shift_assignments', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();
            $table->string('status')->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── biometric_punches ──────────────────────────────────────────────
        Schema::create('biometric_punches', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('punch_type');   // clock_in|clock_out|break_start|break_end
            $table->string('punch_source'); // device|mobile|gps|manual
            $table->dateTime('punched_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->timestamps();
        });

        // ── employment_lifecycle_states ────────────────────────────────────
        if (! Schema::hasTable('employment_lifecycle_states')) {
            Schema::create('employment_lifecycle_states', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
                $table->string('status');
                $table->string('previous_status')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->dateTime('effective_at')->nullable();
                $table->timestamps();
            });
        }

        // ── staff_profiles alterations ─────────────────────────────────────
        Schema::table('staff_profiles', static function (Blueprint $table) {
            if (! Schema::hasColumn('staff_profiles', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->index()->after('company_id');
            }
            if (! Schema::hasColumn('staff_profiles', 'employment_status')) {
                $table->string('employment_status')->default('active')->after('status');
            }
        });

        // ── leaves alterations ─────────────────────────────────────────────
        Schema::table('leaves', static function (Blueprint $table) {
            if (! Schema::hasColumn('leaves', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (! Schema::hasColumn('leaves', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('leaves', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
        });

        // ── shifts alterations ─────────────────────────────────────────────
        Schema::table('shifts', static function (Blueprint $table) {
            if (! Schema::hasColumn('shifts', 'shift_type')) {
                $table->string('shift_type')->default('standard')->after('status');
            }
            if (! Schema::hasColumn('shifts', 'recurring_days')) {
                $table->json('recurring_days')->nullable()->after('shift_type');
            }
            if (! Schema::hasColumn('shifts', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('recurring_days');
            }
            if (! Schema::hasColumn('shifts', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('location_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', static function (Blueprint $table) {
            foreach (['shift_type', 'recurring_days', 'location_id', 'is_published'] as $col) {
                if (Schema::hasColumn('shifts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('leaves', static function (Blueprint $table) {
            foreach (['approved_by', 'approved_at', 'rejection_reason'] as $col) {
                if (Schema::hasColumn('leaves', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('staff_profiles', static function (Blueprint $table) {
            foreach (['department_id', 'employment_status'] as $col) {
                if (Schema::hasColumn('staff_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('employment_lifecycle_states');
        Schema::dropIfExists('biometric_punches');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('departments');
    }
};
