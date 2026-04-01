<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSuiteAccount extends Model
{
    protected $table = 'business_suite_accounts';

    protected $fillable = [
        'title',
        'subtitle',
        'key',
        'link',
        'icon',
        'is_active',
    ];
}
