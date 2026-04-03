<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('module_settings') || !Schema::hasTable('companies')) {
            return;
        }

        $now = Carbon::now();

        // Ensure Titan Talk exists in modules table
        $moduleId = DB::table('modules')
            ->where('module_name', 'titan-talk')
            ->value('id');

        if (! $moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'module_name'   => 'titan-talk',
                'description'   => 'Titan Talk – multi-channel AI communication (web, WhatsApp, Telegram, Messenger, Voice) powered by Titan Core.',
                'is_superadmin' => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // Enable Titan Talk for all companies in module_settings
        $companies = DB::table('companies')->select('id')->get();

        foreach ($companies as $company) {
            foreach (['admin', 'employee', 'client'] as $type) {
                $exists = DB::table('module_settings')
                    ->where('company_id', $company->id)
                    ->where('module_name', 'titan-talk')
                    ->where('type', $type)
                    ->exists();

                if (! $exists) {
                    DB::table('module_settings')->insert([
                        'company_id' => $company->id,
                        'module_name'=> 'titan-talk',
                        'status'     => 'active',
                        'type'       => $type,
                        'is_allowed' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('module_settings')) {
            return;
        }

        DB::table('module_settings')
            ->where('module_name', 'titan-talk')
            ->delete();

        DB::table('modules')
            ->where('module_name', 'titan-talk')
            ->delete();
    }
};
