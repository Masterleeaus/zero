<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('package_settings') && Schema::hasColumn('package_settings', 'modules')) {
            DB::statement("
                UPDATE package_settings
                SET modules = JSON_ARRAY_APPEND(
                    CASE
                        WHEN modules IS NULL OR modules = '' THEN JSON_ARRAY()
                        ELSE CAST(modules AS JSON)
                    END,
                    '$',
                    'aiconverse'
                )
                WHERE JSON_CONTAINS(
                        COALESCE(CAST(modules AS JSON), JSON_ARRAY()),
                        JSON_QUOTE('aiconverse')
                    ) = 0
            ");
        }

        if (Schema::hasTable('companies') && Schema::hasTable('module_settings')) {
            $types = ['admin','employee','client'];
            $now = now();
            $companyIds = DB::table('companies')->pluck('id');
            foreach ($companyIds as $cid) {
                foreach ($types as $type) {
                    $exists = DB::table('module_settings')
                        ->where('company_id', $cid)
                        ->where('module_name', 'aiconverse')
                        ->where('type', $type)
                        ->exists();
                    if (!$exists) {
                        DB::table('module_settings')->insert([
                            'company_id' => $cid,
                            'module_name'=> 'aiconverse',
                            'status'     => 'active',
                            'type'       => $type,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'is_allowed' => 1,
                        ]);
                    }
                }
            }
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('package_settings') && Schema::hasColumn('package_settings', 'modules')) {
            DB::statement("
                UPDATE package_settings
                SET modules = CASE
                    WHEN modules IS NULL OR modules = '' THEN JSON_ARRAY()
                    ELSE JSON_REMOVE(CAST(modules AS JSON),
                        JSON_UNQUOTE(JSON_SEARCH(CAST(modules AS JSON), 'one', 'aiconverse'))
                    )
                END
                WHERE JSON_SEARCH(CAST(modules AS JSON), 'one', 'aiconverse') IS NOT NULL
            ");
        }
        if (Schema::hasTable('module_settings')) {
            DB::table('module_settings')->where('module_name', 'aiconverse')->delete();
        }
    }
};
