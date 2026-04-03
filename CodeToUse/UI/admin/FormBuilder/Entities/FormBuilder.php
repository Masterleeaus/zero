<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormBuilder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'created_by',
        'is_active',
        'is_lead_active',
        'workspace',
    ];

    public static $fieldTypes = [
        'text' => 'Text',
        'email' => 'Email',
        'number' => 'Number',
        'date' => 'Date',
        'textarea' => 'Textarea',
    ];

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FormBuilderFactory::new();
    }

    public function response()
    {
        return $this->hasMany(FormResponse::class, 'form_id', 'id');
    }

    public function form_field()
    {
        return $this->hasMany(FormField::class, 'form_id', 'id');
    }

    public function fieldResponse()
    {
        return $this->hasOne(FormFieldResponse::class, 'form_id', 'id');
    }
}
