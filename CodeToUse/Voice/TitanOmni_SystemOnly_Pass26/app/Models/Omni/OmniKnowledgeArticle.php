<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmniKnowledgeArticle extends Model
{
    use HasFactory;

    protected $table = 'omni_knowledge_articles';

    protected $fillable = [
        'company_id',
        'agent_id',
        'title',
        'source_type',
        'source_ref',
        'content',
        'summary',
        'tags',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }
}
