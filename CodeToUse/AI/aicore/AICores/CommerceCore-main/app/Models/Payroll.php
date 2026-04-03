<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Payroll extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, LogsActivity;

    protected $fillable = [
        'employee_id', 'month', 'basic_salary', 
        'bonus', 'deduction', 'net_salary', 'status', 'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
