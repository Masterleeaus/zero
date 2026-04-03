<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Entities\Concerns\BelongsToTenant;

class DocumentRequest extends Model
{
    use BelongsToTenant;

    protected $table = 'documents_requests';

    protected $fillable = [
        'tenant_id',
        'requested_by',
        'document_id',
        'title',
        'recipient_email',
        'recipient_name',
        'message',
        'due_at',
        'status',
        'token',
        'sent_at',
        'received_at',
        'cancelled_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function uploads()
    {
        return $this->hasMany(DocumentRequestUpload::class, 'request_id');
    }
}
