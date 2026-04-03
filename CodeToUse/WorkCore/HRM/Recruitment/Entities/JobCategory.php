<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobCategoryFactory::new();
    }
}
