<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $perms = [
            ['name' => 'documents.view', 'display_name' => 'View Documents'],
            ['name' => 'documents.create', 'display_name' => 'Create Documents'],
            ['name' => 'documents.update', 'display_name' => 'Update Documents'],
            ['name' => 'documents.delete', 'display_name' => 'Delete Documents'],
            ['name' => 'documents.version', 'display_name' => 'View Document Versions'],
            ['name' => 'documents.share', 'display_name' => 'Create Share Links'],
            ['name' => 'documents.link', 'display_name' => 'Link Documents'],
        ];

        foreach ($perms as $p) {
            $exists = DB::table('permissions')->where('name', $p['name'])->exists();
            if (! $exists) {
                DB::table('permissions')->insert(array_merge($p, [
                    'module_name' => 'Documents',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->whereIn('name', [
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.version',
            'documents.share',
            'documents.link',
        ])->delete();
    }
};
