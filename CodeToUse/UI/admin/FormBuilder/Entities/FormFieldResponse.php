<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormFieldResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'subject_id',
        'name_id',
        'email_id',
        'user_id',
        'pipeline_id',
        'workspace',
    ];

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FormFieldResponseFactory::new();
    }
}
