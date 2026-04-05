<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * OmniKnowledgeArticle — Company-scoped knowledge base article for RAG.
 *
 * Feeds into KnowledgeManager for AI retrieval context.
 * Will supersede chatbot_data / chatbot_data_vectors in Pass 08 (dual-write migration).
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $title
 * @property string      $source_type
 * @property string|null $source_ref
 * @property string|null $content
 * @property string|null $summary
 * @property string|null $embedding_model
 * @property string      $status
 * @property array|null  $tags
 * @property array|null  $metadata
 */
class OmniKnowledgeArticle extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_knowledge_articles';

    protected $fillable = [
        'uuid',
        'company_id',
        'agent_id',
        'title',
        'source_type',
        'source_ref',
        'content',
        'summary',
        'embedding_model',
        'status',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'tags'     => 'array',
        'metadata' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }
}
