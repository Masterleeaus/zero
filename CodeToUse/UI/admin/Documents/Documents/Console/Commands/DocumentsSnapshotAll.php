<?php

namespace Modules\Documents\Console\Commands;

use Illuminate\Console\Command;
use Modules\Documents\Entities\Document;
use Modules\Documents\Services\Versioning\DocumentSnapshotService;

class DocumentsSnapshotAll extends Command
{
    protected $signature = 'documents:snapshot-all {--tenant=} {--reason=manual}';
    protected $description = 'Create a snapshot version for all documents (tenant-scoped)';

    public function handle(DocumentSnapshotService $snapshots): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('documents')) {
            $this->error('documents table not found');
            return self::FAILURE;
        }

        $tenant = $this->option('tenant');
        $q = Document::query();
        if ($tenant) {
            $q->where('tenant_id', $tenant);
        }

        $count = 0;
        $q->orderBy('id')->chunk(200, function ($docs) use ($snapshots, &$count) {
            foreach ($docs as $doc) {
                $snapshots->snapshot($doc, null, $this->option('reason'));
                $count++;
            }
        });

        $this->info("Snapshot created for {$count} documents");
        return self::SUCCESS;
    }
}
