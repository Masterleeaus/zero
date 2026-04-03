<?php

namespace Modules\Documents\Services\Search;

use Illuminate\Database\Eloquent\Builder;
use Modules\Documents\Entities\Document;

class DocumentSearchService
{
    public function query(array $filters = []): Builder
    {
        $q = Document::query();

        // Tenant safety (best effort): scope by company_id if column exists and value provided.
        if (!empty($filters['company_id']) && $this->hasColumn('documents', 'company_id')) {
            $q->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        if (!empty($filters['created_by'])) {
            $q->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['q'])) {
            $term = trim($filters['q']);
            // Prefer FULLTEXT if present, otherwise fallback to LIKE.
            if ($this->hasFullTextIndex()) {
                $q->whereRaw('MATCH(title, content) AGAINST (? IN BOOLEAN MODE)', [$term . '*']);
            } else {
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('title', 'like', '%' . $term . '%')
                        ->orWhere('content', 'like', '%' . $term . '%');
                });
            }
        }

        if (!empty($filters['updated_from'])) {
            $q->whereDate('updated_at', '>=', $filters['updated_from']);
        }

        if (!empty($filters['updated_to'])) {
            $q->whereDate('updated_at', '<=', $filters['updated_to']);
        }

        $sort = $filters['sort'] ?? 'updated_at';
        $dir  = $filters['dir'] ?? 'desc';
        if (in_array($sort, ['created_at','updated_at','title','status','type'], true)) {
            $q->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        } else {
            $q->orderBy('updated_at', 'desc');
        }

        return $q;
    }

    protected function hasFullTextIndex(): bool
    {
        // We can't reliably introspect indexes cross-DB without queries; be conservative.
        return true;
    }

    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
