<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Documents\Traits\CompanyScoped;

class DocumentFolder extends Model
{
    use CompanyScoped;
    use SoftDeletes;

    protected $table = 'document_folders';

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class, 'folder_id');
    }
}