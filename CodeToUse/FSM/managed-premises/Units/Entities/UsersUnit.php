<?php

namespace Modules\Units\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\HasCompany;
use Modules\Units\Entities\Unit;

class UsersUnit extends BaseModel
{
    use HasCompany;

    protected $table = 'users_units';
    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'unit_id');
    }
}
