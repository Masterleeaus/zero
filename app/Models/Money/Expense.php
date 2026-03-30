<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBetweenDates(Builder $query, ?string $start, ?string $end): Builder
    {
        return $query
            ->when($start, fn ($q) => $q->whereDate('expense_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('expense_date', '<=', $end));
    }

    public static function totalForCompany(int $companyId): float
    {
        return (float) static::query()
            ->where('company_id', $companyId)
            ->sum(DB::raw('COALESCE(amount,0)'));
    }

    public static function totalsByCategory(int $companyId): array
    {
        return static::query()
            ->where('company_id', $companyId)
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->pluck('total', 'expense_category_id')
            ->toArray();
    }
}
