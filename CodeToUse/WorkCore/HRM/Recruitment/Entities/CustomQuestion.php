<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'is_required',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\CustomQuestionFactory::new();
    }

    public static $is_required = [
        'yes' => 'Yes',
        'no' => 'No',
    ];
}
