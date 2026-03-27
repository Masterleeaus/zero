<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablesWithTeamId = [
            'users',
            'user_openai',
            'user_openai_chat',
            'folders',
        ];

        foreach ($tablesWithTeamId as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'company_id')) {
                    // Soft reference to companies to avoid breaking existing installs without the table/seeded data.
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });

        if (Schema::hasColumn('teams', 'company_id')) {
            DB::table('teams')
                ->whereNull('company_id')
                ->update(['company_id' => DB::raw('id')]);
        }
        if (Schema::hasColumn('teams', 'company_id')) {
            DB::table('teams')
                ->whereNull('company_id')
                ->update(['company_id' => DB::raw('id')]);
        }

        Schema::table('team_members', function (Blueprint $table) {
            if (! Schema::hasColumn('team_members', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('team_id')->index();
            }
        });

        foreach ($tablesWithTeamId as $tableName) {
            if (Schema::hasColumn($tableName, 'company_id') && Schema::hasColumn($tableName, 'team_id')) {
                DB::table($tableName)
                    ->whereNull('company_id')
                    ->update(['company_id' => DB::raw('team_id')]);
            }
        }

        if (
            Schema::hasColumn('team_members', 'company_id') &&
            Schema::hasColumn('team_members', 'team_id') &&
            Schema::hasColumn('teams', 'company_id')
        ) {
            DB::table('team_members')
                ->whereNull('company_id')
                ->chunkById(100, function ($members) {
                    $teamIds = $members->pluck('team_id')->filter()->unique()->values();

                    if ($teamIds->isEmpty()) {
                        return;
                    }

                    $teams = DB::table('teams')
                        ->whereIn('id', $teamIds)
                        ->pluck('company_id', 'id');

                    foreach ($members as $member) {
                        if (! $member->team_id) {
                            continue;
                        }

                        $companyId = $teams[$member->team_id] ?? null;

                        if ($companyId === null) {
                            continue;
                        }

                        DB::table('team_members')
                            ->where('id', $member->id)
                            ->update(['company_id' => $companyId]);
                    }
                });
        }
    }

    public function down(): void
    {
        $tablesWithCompanyId = [
            'users',
            'user_openai',
            'user_openai_chat',
            'folders',
            'teams',
            'team_members',
        ];

        foreach ($tablesWithCompanyId as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }
    }
};
