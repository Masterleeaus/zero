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
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });

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
                ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
                ->whereNull('team_members.company_id')
                ->update(['team_members.company_id' => DB::raw('teams.company_id')]);
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
