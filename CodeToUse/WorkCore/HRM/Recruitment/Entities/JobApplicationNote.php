<?php

namespace Modules\Recruitment\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplicationNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'note_created',
        'note',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobApplicationNoteFactory::new();
    }

    public function noteCreated()
    {
        return $this->hasOne(User::class, 'id', 'note_created');
    }
}
