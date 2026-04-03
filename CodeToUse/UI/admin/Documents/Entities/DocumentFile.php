<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Documents\Traits\CompanyScoped;

class DocumentFile extends Model
{
    use CompanyScoped;
    use SoftDeletes;

    protected $table = 'document_files';

    protected $guarded = ['id'];

    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}