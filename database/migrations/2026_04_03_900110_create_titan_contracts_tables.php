<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MODULE 04 — TitanContracts: Agreement Entitlement Engine
 *
 * - Extends service_agreements with contract management columns
 * - Creates contract_entitlements, contract_sla_breaches, contract_renewals
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Extend service_agreements ─────────────────────────────────────────
        Schema::table('service_agreements', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_agreements', 'deal_id')) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('quote_id');
                $table->index('deal_id');
            }
            if (! Schema::hasColumn('service_agreements', 'contract_number')) {
                $table->string('contract_number')->nullable()->after('deal_id');
                $table->unique('contract_number');
            }
            if (! Schema::hasColumn('service_agreements', 'contract_type')) {
                $table->string('contract_type')->default('rolling')->after('contract_number')
                    ->comment('fixed_term|rolling|pay_as_you_go|retainer');
            }
            if (! Schema::hasColumn('service_agreements', 'billing_cycle')) {
                $table->string('billing_cycle')->nullable()->after('contract_type')
                    ->comment('monthly|quarterly|annually');
            }
            if (! Schema::hasColumn('service_agreements', 'billing_amount')) {
                $table->decimal('billing_amount', 10, 2)->nullable()->after('billing_cycle');
            }
            if (! Schema::hasColumn('service_agreements', 'sla_response_hours')) {
                $table->unsignedInteger('sla_response_hours')->nullable()->after('billing_amount');
            }
            if (! Schema::hasColumn('service_agreements', 'sla_resolution_hours')) {
                $table->unsignedInteger('sla_resolution_hours')->nullable()->after('sla_response_hours');
            }
            if (! Schema::hasColumn('service_agreements', 'auto_renews')) {
                $table->boolean('auto_renews')->default(false)->after('sla_resolution_hours');
            }
            if (! Schema::hasColumn('service_agreements', 'renewal_notice_days')) {
                $table->unsignedInteger('renewal_notice_days')->default(30)->after('auto_renews');
            }
            if (! Schema::hasColumn('service_agreements', 'renewed_from_id')) {
                $table->unsignedBigInteger('renewed_from_id')->nullable()->after('renewal_notice_days');
                $table->index('renewed_from_id');
            }
            if (! Schema::hasColumn('service_agreements', 'health_score')) {
                $table->unsignedTinyInteger('health_score')->default(100)->after('renewed_from_id');
            }
            if (! Schema::hasColumn('service_agreements', 'health_flags')) {
                $table->json('health_flags')->nullable()->after('health_score');
            }
        });

        // ── contract_entitlements ─────────────────────────────────────────────
        Schema::create('contract_entitlements', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('agreement_id');
            $table->string('service_type');
            $table->unsignedInteger('max_visits')->nullable();
            $table->unsignedInteger('visits_used')->default(0);
            $table->decimal('max_hours', 8, 2)->nullable();
            $table->decimal('hours_used', 8, 2)->default(0);
            $table->string('period_type')->default('annual')->comment('monthly|quarterly|annual|contract');
            $table->date('resets_on')->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'agreement_id']);
            $table->index(['agreement_id', 'service_type']);
            $table->foreign('agreement_id')->references('id')->on('service_agreements')->onDelete('cascade');
        });

        // ── contract_sla_breaches ─────────────────────────────────────────────
        Schema::create('contract_sla_breaches', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('agreement_id');
            $table->unsignedBigInteger('job_id');
            $table->string('breach_type')->comment('response|resolution');
            $table->unsignedInteger('sla_hours');
            $table->decimal('actual_hours', 8, 2);
            $table->timestamp('breached_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'agreement_id']);
            $table->index(['agreement_id', 'breach_type']);
            $table->foreign('agreement_id')->references('id')->on('service_agreements')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('service_jobs')->onDelete('cascade');
        });

        // ── contract_renewals ─────────────────────────────────────────────────
        Schema::create('contract_renewals', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('agreement_id');
            $table->unsignedBigInteger('renewed_to_id');
            $table->timestamp('renewed_at');
            $table->unsignedBigInteger('renewed_by')->nullable();
            $table->date('previous_expiry')->nullable();
            $table->date('new_expiry')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'agreement_id']);
            $table->foreign('agreement_id')->references('id')->on('service_agreements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_renewals');
        Schema::dropIfExists('contract_sla_breaches');
        Schema::dropIfExists('contract_entitlements');

        Schema::table('service_agreements', static function (Blueprint $table) {
            $columnsToDrop = [
                'deal_id', 'contract_number', 'contract_type', 'billing_cycle',
                'billing_amount', 'sla_response_hours', 'sla_resolution_hours',
                'auto_renews', 'renewal_notice_days', 'renewed_from_id',
                'health_score', 'health_flags',
            ];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('service_agreements', $col)) {
                    if (in_array($col, ['deal_id', 'renewed_from_id'], true)) {
                        $table->dropIndex([$col]);
                    }
                    if ($col === 'contract_number') {
                        $table->dropUnique(['contract_number']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
