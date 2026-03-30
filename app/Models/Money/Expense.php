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
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public static function totalsByMonth(int $companyId, int $months = 6): Collection
    {
        $months = min(max($months, 1), 120);
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $expression = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', expense_date)",
            'pgsql' => "to_char(expense_date, 'YYYY-MM')",
            default => "DATE_FORMAT(expense_date, '%Y-%m')",
        };

        return static::query()
            ->where('company_id', $companyId)
            ->whereDate('expense_date', '>=', $start->toDateString())
            ->selectRaw("{$expression} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
