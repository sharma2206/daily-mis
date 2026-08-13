<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PackageConsumption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch',
        'consumption_date',
        'amount',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consumption_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Scope a query to only include records for a specific branch.
     *
     * @param Builder $query
     * @param string $branch
     * @return Builder
     */
    public function scopeForBranch(Builder $query, string $branch): Builder
    {
        return $query->where('branch', $branch);
    }

    /**
     * Scope a query to only include records for a specific date.
     *
     * @param Builder $query
     * @param string $date
     * @return Builder
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('consumption_date', $date);
    }

    /**
     * Scope a query to only include records for a specific month.
     *
     * @param Builder $query
     * @param string $date
     * @return Builder
     */
    public function scopeForMonth(Builder $query, string $date): Builder
    {
        $carbonDate = Carbon::parse($date);
        
        return $query->whereYear('consumption_date', $carbonDate->year)
                     ->whereMonth('consumption_date', $carbonDate->month);
    }
}
