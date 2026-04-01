<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'stage_id')) {
                $table->unsignedBigInteger('stage_id')->nullable()->after('status')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'job_type_id')) {
                $table->unsignedBigInteger('job_type_id')->nullable()->after('stage_id')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('job_type_id')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'priority')) {
                $table->string('priority')->default('normal')->after('template_id');
            }
            if (! Schema::hasColumn('service_jobs', 'sequence')) {
                $table->unsignedInteger('sequence')->default(10)->after('priority');
            }
            if (! Schema::hasColumn('service_jobs', 'territory_id')) {
                $table->unsignedBigInteger('territory_id')->nullable()->after('sequence')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('territory_id')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable()->after('branch_id')->index();
            }
            if (! Schema::hasColumn('service_jobs', 'scheduled_date_start')) {
                $table->dateTime('scheduled_date_start')->nullable()->after('district_id');
            }
            if (! Schema::hasColumn('service_jobs', 'scheduled_duration')) {
                $table->decimal('scheduled_duration', 5, 2)->default(0)->after('scheduled_date_start')->comment('Planned hours');
            }
            if (! Schema::hasColumn('service_jobs', 'scheduled_date_end')) {
                $table->dateTime('scheduled_date_end')->nullable()->after('scheduled_duration');
            }
            if (! Schema::hasColumn('service_jobs', 'date_start')) {
                $table->dateTime('date_start')->nullable()->after('scheduled_date_end')->comment('Actual start');
            }
            if (! Schema::hasColumn('service_jobs', 'date_end')) {
                $table->dateTime('date_end')->nullable()->after('date_start')->comment('Actual end');
            }
            if (! Schema::hasColumn('service_jobs', 'todo')) {
                $table->text('todo')->nullable()->after('date_end');
            }
            if (! Schema::hasColumn('service_jobs', 'resolution')) {
                $table->text('resolution')->nullable()->after('todo');
            }
            if (! Schema::hasColumn('service_jobs', 'signed_by')) {
                $table->string('signed_by')->nullable()->after('resolution');
            }
            if (! Schema::hasColumn('service_jobs', 'signed_on')) {
                $table->dateTime('signed_on')->nullable()->after('signed_by');
            }
            if (! Schema::hasColumn('service_jobs', 'require_signature')) {
                $table->boolean('require_signature')->default(false)->after('signed_on');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            $cols = [
                'stage_id', 'job_type_id', 'template_id', 'priority', 'sequence',
                'territory_id', 'branch_id', 'district_id',
                'scheduled_date_start', 'scheduled_duration', 'scheduled_date_end',
                'date_start', 'date_end', 'todo', 'resolution',
                'signed_by', 'signed_on', 'require_signature',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('service_jobs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
