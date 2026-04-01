<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseFile extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'article_id',
        'name',
        'file_path',
        'file_size',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'article_id');
    }
}
