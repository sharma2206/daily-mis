<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MisReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch',
        'report_date',
        'occupancy',
        'occupancy_pct',
        'admission',
        'discharge',
        'total_op',
        'er_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'occupancy_pct' => 'decimal:2',
            'report_data' => 'array',
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
        return $query->whereDate('report_date', $date);
    }

    /**
     * Scope: all records in the month up to (and including) the given date.
     *
     * @param Builder $query
     * @param string $date
     * @return Builder
     */
    public function scopeForMonthUpTo(Builder $query, string $date): Builder
    {
        $carbonDate = Carbon::parse($date);
        $monthStart = $carbonDate->copy()->startOfMonth()->toDateString();

        return $query->whereDate('report_date', '>=', $monthStart)
            ->whereDate('report_date', '<=', $date);
    }
}
