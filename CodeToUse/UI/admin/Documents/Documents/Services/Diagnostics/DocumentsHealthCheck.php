<?php

namespace Modules\Documents\Services\Diagnostics;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Documents\Entities\DocumentsTemplate;

class DocumentsHealthCheck
{
    /**
     * Run lightweight checks only — must never throw.
     *
     * @param array{fix?:bool} $opts
     * @return array{ok:bool,messages:array<int,array{level:string,text:string}>}
     */
    public function run(array $opts = []): array
    {
        $fix = (bool)($opts['fix'] ?? false);

        $messages = [];
        $ok = true;

        // 1) Key routes should exist (tenant context)
        $routeNames = [
            'documents.index',
            'documents.show',
            'documents.templates.index',
        ];

        foreach ($routeNames as $name) {
            if (!Route::has($name)) {
                $ok = false;
                $messages[] = ['level' => 'error', 'text' => "Missing route: {$name}"];
            }
        }

        // 2) Titan Zero integration routes should exist if button partial is used
        $tzCandidates = [
            'titan.zero.index',
            'titan.zero.heroes.index',
            'titan.zero.heroes.ask',
        ];

        $tzFound = false;
        foreach ($tzCandidates as $name) {
            if (Route::has($name)) { $tzFound = true; break; }
        }

        if (!$tzFound) {
            $messages[] = [
                'level' => 'warn',
                'text' => 'Titan Zero/Heroes routes not detected. Ask Titan Zero button will be hidden via Route::has() guards.',
            ];
        } else {
            $messages[] = ['level' => 'info', 'text' => 'Titan Zero/Heroes routes detected.'];
        }

        // 3) Templates installed?
        try {
            if (class_exists(DocumentsTemplate::class) && Schema::hasTable('documents_templates')) {
                $count = DocumentsTemplate::query()->count();
                if ($count === 0) {
                    $messages[] = [
                        'level' => 'warn',
                        'text' => "No templates found in documents_templates. Run: php artisan module:seed Documents (or db:seed DocumentsTemplateSeeder)",
                    ];
                } else {
                    $messages[] = ['level' => 'info', 'text' => "Templates present: {$count}"];
                }
            } else {
                $messages[] = [
                    'level' => 'warn',
                    'text' => 'Templates table/model not detected (documents_templates). If this is unexpected, ensure migrations ran.',
                ];
            }
        } catch (\Throwable $e) {
            $ok = false;
            $messages[] = ['level' => 'error', 'text' => 'Templates check failed: ' . $e->getMessage()];
        }

        // 4) Storage paths (non-destructive)
        try {
            $paths = [
                storage_path('app/documents'),
                storage_path('app/documents/share'),
                storage_path('app/documents/uploads'),
            ];

            foreach ($paths as $p) {
                if (!is_dir($p)) {
                    if ($fix) {
                        @mkdir($p, 0755, true);
                    }
                    if (!is_dir($p)) {
                        $ok = false;
                        $messages[] = ['level' => 'error', 'text' => "Missing storage directory: {$p}"];
                    } else {
                        $messages[] = ['level' => 'info', 'text' => "Created storage directory: {$p}"];
                    }
                }
            }
        } catch (\Throwable $e) {
            $ok = false;
            $messages[] = ['level' => 'error', 'text' => 'Storage path check failed: ' . $e->getMessage()];
        }

        // 5) Sanity: key tables exist
        $tables = [
            'documents',
            'documents_templates',
            'document_folders',
            'document_files',
        ];

        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) {
                $ok = false;
                $messages[] = ['level' => 'error', 'text' => "Missing DB table: {$t}"];
            }
        }

        if ($ok) {
            $messages[] = ['level' => 'info', 'text' => 'DB tables look OK.'];
        }

        return [
            'ok' => $ok,
            'messages' => $messages,
        ];
    }
}
