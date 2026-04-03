<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentRequestUpload extends Model
{
    use BelongsToTenant;

    protected $table = 'documents_request_uploads';

    protected $fillable = [
        'tenant_id',
        'request_id',
        'document_file_id',
        'original_name',
        'path',
        'size',
        'mime',
        'ip',
        'user_agent',
    ];

    public function request()
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }

    public function documentFile()
    {
        return $this->belongsTo(DocumentFile::class, 'document_file_id');
    }
}
