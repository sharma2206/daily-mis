<?php

namespace App\Services;

use App\Enums\Branch;
use App\Models\BillItem;
use App\Models\CashierCollection;
use App\Models\MisReport;
use App\Models\PackageConsumption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MISService
{
    /**
     * Generate the MIS report — cached for 30 minutes per branch+date.
     */
    public function generateMIS(Branch $branch, string $date, array $volumeData = []): array
    {
        // On a GET request with no volumeData, serve from cache if available
        if (empty($volumeData)) {
            return Cache::remember(
                "mis:{$branch->value}:{$date}",
                1800,
                fn() => $this->buildReport($branch, $date, [])
            );
        }

        // On upload (POST with volumeData), rebuild fresh and bust cache
        $result = $this->buildReport($branch, $date, $volumeData);
        Cache::forget("mis:{$branch->value}:{$date}");
        Cache::put("mis:{$branch->value}:{$date}", $result, 1800);
        return $result;
    }

    /**
     * Core report builder — all heavy DB work happens here.
     */
    private function buildReport(Branch $branch, string $date, array $volumeData): array
    {
        // If no volume data, load from stored report
        if (empty($volumeData)) {
            $existing = MisReport::where('branch', $branch->value)
                ->where('report_date', $date)
                ->first();
            if ($existing) {
                $volumeData = [
                    'ftd' => [
                        'occupancy'     => $existing->occupancy,
                        'occupancy_pct' => (float) $existing->occupancy_pct,
                        'admission'     => $existing->admission,
                        'discharge'     => $existing->discharge,
                        'total_op'      => $existing->total_op,
                        'er_count'      => $existing->er_count ?? 0,
                    ]
                ];
            }
        }

        // ── Single consolidated query per period for bill_items ──────────
        $billFtd = $this->getBillItemsAggregated($branch, $date, 'ftd');
        $billMtd = $this->getBillItemsAggregated($branch, $date, 'mtd');

        $sales      = $this->extractSalesData($billFtd, $billMtd);
        $discount   = $this->extractDiscountData($billFtd, $billMtd);
        $refund     = $this->extractRefundData($billFtd, $billMtd);
        $mri        = $branch === Branch::ORAGADAM
            ? $this->emptyMri()
            : $this->extractMriData($billFtd, $billMtd);

        // ── Two cashier_collections queries (one per period) ─────────────
        $collection = $this->getCollectionData($branch, $date);

        // ── Volume (uses mis_reports + one bill_items OP count query) ────
        $volume = $this->buildVolumePayload($branch, $date, $volumeData);

        $data = [
            'branch'       => $branch->label(),
            'branch_key'   => $branch->value,
            'date'         => $date,
            'sales'        => $sales,
            'collection'   => $collection,
            'discount'     => $discount,
            'refund'       => $refund,
            'mri'          => $mri,
            'volume'       => $volume,
            'generated_at' => now()->toDateTimeString(),
        ];

        $this->applyBranchAdjustments($branch, $data, $date);
        $data['totals'] = $this->calculateTotals($data);
        $this->persistReport($branch, $date, $data, $volumeData);

        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSOLIDATED QUERY: one pass over bill_items per period
    // Replaces the old getSalesData / getDiscountData / getRefundData /
    // getMriData / OP-count queries (was ~8 queries → now 2)
    // ─────────────────────────────────────────────────────────────────────
    private function getBillItemsAggregated(Branch $branch, string $date, string $period): object
    {
        $selectRaw = "
            -- SALES (Sale + Refund, net_amount)
            SUM(CASE WHEN service_type = 'Pharmacy' AND status IN ('Sale','Refund') THEN net_amount ELSE 0 END) AS sales_ph,
            SUM(CASE WHEN patient_type = 'OP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') THEN net_amount ELSE 0 END) AS sales_op,
            SUM(CASE WHEN patient_type = 'IP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') THEN net_amount ELSE 0 END) AS sales_ip,
            SUM(CASE WHEN patient_type = 'ER' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') THEN net_amount ELSE 0 END) AS sales_er,

            -- DISCOUNT 99% (partial — net_amount != 0, amount = gross discount)
            SUM(CASE WHEN service_type = 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount != 0 THEN amount ELSE 0 END) AS disc_partial_ph,
            SUM(CASE WHEN patient_type = 'OP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount != 0 THEN amount ELSE 0 END) AS disc_partial_op,
            SUM(CASE WHEN patient_type = 'IP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount != 0 THEN amount ELSE 0 END) AS disc_partial_ip,
            SUM(CASE WHEN patient_type = 'ER' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount != 0 THEN amount ELSE 0 END) AS disc_partial_er,

            -- DISCOUNT 100% (full — net_amount = 0)
            SUM(CASE WHEN service_type = 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount = 0 THEN amount ELSE 0 END) AS disc_full_ph,
            SUM(CASE WHEN patient_type = 'OP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount = 0 THEN amount ELSE 0 END) AS disc_full_op,
            SUM(CASE WHEN patient_type = 'IP' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount = 0 THEN amount ELSE 0 END) AS disc_full_ip,
            SUM(CASE WHEN patient_type = 'ER' AND service_type != 'Pharmacy' AND status IN ('Sale','Refund') AND net_amount = 0 THEN amount ELSE 0 END) AS disc_full_er,

            -- REFUND (ABS amount)
            SUM(CASE WHEN service_type = 'Pharmacy' AND status = 'Refund' THEN ABS(amount) ELSE 0 END) AS ref_ph,
            SUM(CASE WHEN patient_type = 'OP' AND service_type != 'Pharmacy' AND status = 'Refund' THEN ABS(amount) ELSE 0 END) AS ref_op,
            SUM(CASE WHEN patient_type = 'IP' AND service_type != 'Pharmacy' AND status = 'Refund' THEN ABS(amount) ELSE 0 END) AS ref_ip,
            SUM(CASE WHEN patient_type = 'ER' AND service_type != 'Pharmacy' AND status = 'Refund' THEN ABS(amount) ELSE 0 END) AS ref_er,

            -- MRI
            SUM(CASE WHEN sub_department = 'MRI' AND patient_type = 'OP' THEN quantity ELSE 0 END) AS mri_op_count,
            SUM(CASE WHEN sub_department = 'MRI' AND patient_type = 'OP' THEN net_amount ELSE 0 END) AS mri_op_revenue,
            SUM(CASE WHEN sub_department = 'MRI' AND patient_type IN ('IP','ER') THEN quantity ELSE 0 END) AS mri_ip_count,
            SUM(CASE WHEN sub_department = 'MRI' AND patient_type IN ('IP','ER') THEN net_amount ELSE 0 END) AS mri_ip_revenue,

            -- OP count
            SUM(CASE WHEN patient_type = 'OP' AND service_type = 'OP Consultation' THEN quantity ELSE 0 END) AS op_consult_count
        ";

        $q = BillItem::query()->where('branch', $branch->value);
        $q = $this->applyDateRange($q, 'bill_date', $date, $period);

        return $q->selectRaw($selectRaw)->first();
    }

    private function extractSalesData(object $ftd, object $mtd): array
    {
        return [
            'ftd' => ['ph' => r($ftd->sales_ph), 'op' => r($ftd->sales_op), 'ip' => r($ftd->sales_ip), 'er' => r($ftd->sales_er)],
            'mtd' => ['ph' => r($mtd->sales_ph), 'op' => r($mtd->sales_op), 'ip' => r($mtd->sales_ip), 'er' => r($mtd->sales_er)],
        ];
    }

    private function extractDiscountData(object $ftd, object $mtd): array
    {
        return [
            'ftd' => [
                'partial' => ['ph' => r($ftd->disc_partial_ph), 'op' => r($ftd->disc_partial_op), 'ip' => r($ftd->disc_partial_ip), 'er' => r($ftd->disc_partial_er)],
                'full'    => ['ph' => r($ftd->disc_full_ph),    'op' => r($ftd->disc_full_op),    'ip' => r($ftd->disc_full_ip),    'er' => r($ftd->disc_full_er)],
            ],
            'mtd' => [
                'partial' => ['ph' => r($mtd->disc_partial_ph), 'op' => r($mtd->disc_partial_op), 'ip' => r($mtd->disc_partial_ip), 'er' => r($mtd->disc_partial_er)],
                'full'    => ['ph' => r($mtd->disc_full_ph),    'op' => r($mtd->disc_full_op),    'ip' => r($mtd->disc_full_ip),    'er' => r($mtd->disc_full_er)],
            ],
        ];
    }

    private function extractRefundData(object $ftd, object $mtd): array
    {
        return [
            'ftd' => ['ph' => r($ftd->ref_ph), 'op' => r($ftd->ref_op), 'ip' => r($ftd->ref_ip), 'er' => r($ftd->ref_er)],
            'mtd' => ['ph' => r($mtd->ref_ph), 'op' => r($mtd->ref_op), 'ip' => r($mtd->ref_ip), 'er' => r($mtd->ref_er)],
        ];
    }

    private function extractMriData(object $ftd, object $mtd): array
    {
        return [
            'ftd' => [
                'op' => ['count' => (int) ($ftd->mri_op_count ?? 0), 'revenue' => r($ftd->mri_op_revenue)],
                'ip' => ['count' => (int) ($ftd->mri_ip_count ?? 0), 'revenue' => r($ftd->mri_ip_revenue)],
            ],
            'mtd' => [
                'op' => ['count' => (int) ($mtd->mri_op_count ?? 0), 'revenue' => r($mtd->mri_op_revenue)],
                'ip' => ['count' => (int) ($mtd->mri_ip_count ?? 0), 'revenue' => r($mtd->mri_ip_revenue)],
            ],
        ];
    }

    private function emptyMri(): array
    {
        $z = ['op' => ['count' => 0, 'revenue' => 0.0], 'ip' => ['count' => 0, 'revenue' => 0.0]];
        return ['ftd' => $z, 'mtd' => $z];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Collection — 2 queries (one per period, already separate table)
    // ─────────────────────────────────────────────────────────────────────
    private function getCollectionData(Branch $branch, string $date): array
    {
        $selectRaw = "
            SUM(CASE WHEN patient_type IS NULL THEN COALESCE(NULLIF(paid_amount,0),0) ELSE 0 END) as ph_total,
            SUM(CASE WHEN patient_type = 'OP'  THEN COALESCE(NULLIF(paid_amount,0),0) ELSE 0 END) as op_total,
            SUM(CASE WHEN patient_type = 'IP'  THEN COALESCE(NULLIF(paid_amount,0),0) ELSE 0 END) as ip_total,
            SUM(CASE WHEN patient_type = 'ER'  THEN COALESCE(NULLIF(paid_amount,0),0) ELSE 0 END) as er_total
        ";

        $base = CashierCollection::query()->where('branch', $branch->value);
        $ftd  = $this->applyDateRange(clone $base, 'collection_date', $date, 'ftd')->selectRaw($selectRaw)->first();
        $mtd  = $this->applyDateRange(clone $base, 'collection_date', $date, 'mtd')->selectRaw($selectRaw)->first();

        return [
            'ftd' => ['ph' => r($ftd->ph_total), 'op' => r($ftd->op_total), 'ip' => r($ftd->ip_total), 'er' => r($ftd->er_total)],
            'mtd' => ['ph' => r($mtd->ph_total), 'op' => r($mtd->op_total), 'ip' => r($mtd->ip_total), 'er' => r($mtd->er_total)],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Volume
    // ─────────────────────────────────────────────────────────────────────
    public function buildVolumePayload(Branch $branch, string $date, array $volumeData): array
    {
        // OP count — reuse already-fetched aggregate if available, else one query
        $ftdOpCount = (int) $this->applyDateRange(
            BillItem::query()->where('branch', $branch->value)
                ->where('patient_type', 'OP')
                ->where('service_type', 'OP Consultation'),
            'bill_date',
            $date,
            'ftd'
        )->sum('quantity');

        $mtdOpCount = (int) $this->applyDateRange(
            BillItem::query()->where('branch', $branch->value)
                ->where('patient_type', 'OP')
                ->where('service_type', 'OP Consultation'),
            'bill_date',
            $date,
            'mtd'
        )->sum('quantity');

        $ftd = [
            'occupancy'     => round($volumeData['ftd']['occupancy'] ?? 0),
            'occupancy_pct' => round($volumeData['ftd']['occupancy_pct'] ?? 0),
            'admission'     => $volumeData['ftd']['admission'] ?? 0,
            'discharge'     => $volumeData['ftd']['discharge'] ?? 0,
            'total_op'      => $ftdOpCount,
            'er_count'      => $volumeData['ftd']['er_count'] ?? 0,
        ];

        $mtd           = $this->accumulateMtdVolume($branch, $date, $ftd);
        $mtd['total_op'] = $mtdOpCount;

        return ['ftd' => $ftd, 'mtd' => $mtd];
    }

    private function accumulateMtdVolume(Branch $branch, string $date, array $todayFtd): array
    {
        $monthStart = Carbon::parse($date)->startOfMonth()->toDateString();

        // Single query — only the lightweight mis_reports table
        $prev = MisReport::where('branch', $branch->value)
            ->whereDate('report_date', '>=', $monthStart)
            ->whereDate('report_date', '<', $date)
            ->selectRaw('SUM(occupancy) as occ, SUM(admission) as adm, SUM(discharge) as dis,
                         SUM(er_count) as er, SUM(occupancy_pct) as occ_pct, COUNT(*) as days')
            ->first();

        $days  = (int) ($prev->days ?? 0);
        $total = $days + 1; // include today

        return [
            'occupancy'     => round(($prev->occ ?? 0) + $todayFtd['occupancy']),
            'occupancy_pct' => $total > 0 ? round((($prev->occ_pct ?? 0) + $todayFtd['occupancy_pct']) / $total) : 0,
            'admission'     => ($prev->adm ?? 0) + $todayFtd['admission'],
            'discharge'     => ($prev->dis ?? 0) + $todayFtd['discharge'],
            'er_count'      => ($prev->er  ?? 0) + ($todayFtd['er_count'] ?? 0),
        ];
    }

    private function persistReport(Branch $branch, string $date, array $data, array $volumeData): void
    {
        MisReport::updateOrCreate(
            ['branch' => $branch->value, 'report_date' => $date],
            [
                'occupancy'     => $volumeData['ftd']['occupancy'] ?? 0,
                'occupancy_pct' => $volumeData['ftd']['occupancy_pct'] ?? 0,
                'admission'     => $volumeData['ftd']['admission'] ?? 0,
                'discharge'     => $volumeData['ftd']['discharge'] ?? 0,
                'total_op'      => $volumeData['ftd']['total_op'] ?? 0,
                'er_count'      => $volumeData['ftd']['er_count'] ?? 0,
            ]
        );
    }

    private function calculateTotals(array $data): array
    {
        $s  = $data['sales'] ?? [];
        $co = $data['collection'] ?? [];
        return [
            'sales_ftd'      => round(array_sum($s['ftd']  ?? []), 2),
            'sales_mtd'      => round(array_sum($s['mtd']  ?? []), 2),
            'collection_ftd' => round(array_sum($co['ftd'] ?? []), 2),
            'collection_mtd' => round(array_sum($co['mtd'] ?? []), 2),
        ];
    }

    private function applyBranchAdjustments(Branch $branch, array &$data, string $date): void
    {
        if ($branch !== Branch::CHROMEPET) {
            $data['pkg_adjustment'] = ['ftd' => 0.0, 'mtd' => 0.0];
            return;
        }

        $base   = PackageConsumption::query()->where('branch', $branch->value);
        $pkgFtd = (float) $this->applyDateRange(clone $base, 'consumption_date', $date, 'ftd')->sum('amount');
        $pkgMtd = (float) $this->applyDateRange(clone $base, 'consumption_date', $date, 'mtd')->sum('amount');

        $data['sales']['ftd']['ph'] = round($data['sales']['ftd']['ph'] + $pkgFtd, 2);
        $data['sales']['ftd']['ip'] = round($data['sales']['ftd']['ip'] - $pkgFtd, 2);
        $data['sales']['mtd']['ph'] = round($data['sales']['mtd']['ph'] + $pkgMtd, 2);
        $data['sales']['mtd']['ip'] = round($data['sales']['mtd']['ip'] - $pkgMtd, 2);
        $data['pkg_adjustment']     = ['ftd' => round($pkgFtd, 2), 'mtd' => round($pkgMtd, 2)];
    }

    /**
     * Get a summary for both branches on a given date (dashboard endpoint).
     */
    public function getDashboardSummary(string $date): array
    {
        $summary = [];
        foreach (Branch::cases() as $branch) {
            $report = MisReport::where('branch', $branch->value)
                ->whereDate('report_date', $date)
                ->first();

            $summary[$branch->value] = [
                'branch'     => $branch->label(),
                'branch_key' => $branch->value,
                'bed_count'  => $branch->bedCount(),
                'has_data'   => $report !== null,
                'report'     => $report?->report_data,
            ];
        }
        return $summary;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Unified date-range helper (replaces buildPeriodQuery)
    // ─────────────────────────────────────────────────────────────────────
    private function applyDateRange(Builder $q, string $col, string $date, string $period): Builder
    {
        if ($period === 'ftd') {
            return $q->whereDate($col, $date);
        }
        $monthStart = Carbon::parse($date)->startOfMonth()->toDateString();
        return $q->whereDate($col, '>=', $monthStart)->whereDate($col, '<=', $date);
    }
}

// ── small helper — round to 2dp, treat null as 0 ──────────────────────────
if (!function_exists('r')) {
    function r(mixed $v): float
    {
        return round((float)($v ?? 0), 2);
    }
}
