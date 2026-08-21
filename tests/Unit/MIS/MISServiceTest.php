<?php

namespace Tests\Unit\MIS;

use App\Enums\Branch;
use App\Models\BillItem;
use App\Models\CashierCollection;
use App\Models\MisReport;
use App\Models\PackageConsumption;
use App\Services\MISService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MISServiceTest extends TestCase
{
    use RefreshDatabase;

    private MISService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MISService();
    }

    public function test_calculates_ftd_and_mtd_sales_correctly()
    {
        $branch = Branch::CHROMEPET;
        $date = '2026-08-21';
        $monthStart = '2026-08-01';

        // FTD Pharmacy Sale
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'service_type' => 'Pharmacy', 'status' => 'Sale', 'amount' => 100, 'net_amount' => 90
        ]);
        // FTD OP Sale
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'service_type' => 'OP Consultation', 'status' => 'Sale', 'amount' => 200, 'net_amount' => 200
        ]);
        // MTD IP Sale (previous day in month)
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => '2026-08-10', 'patient_type' => 'IP',
            'service_type' => 'Room Rent', 'status' => 'Sale', 'amount' => 500, 'net_amount' => 500
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        // Asserts
        $this->assertEquals(90.0, $report['sales']['ftd']['ph']);
        $this->assertEquals(200.0, $report['sales']['ftd']['op']);
        $this->assertEquals(0.0, $report['sales']['ftd']['ip']);

        $this->assertEquals(90.0, $report['sales']['mtd']['ph']); // FTD is also MTD
        $this->assertEquals(200.0, $report['sales']['mtd']['op']);
        $this->assertEquals(500.0, $report['sales']['mtd']['ip']); // Only MTD
    }

    public function test_calculates_discounts_correctly()
    {
        $branch = Branch::ORAGADAM;
        $date = '2026-08-21';

        // Partial Discount (net_amount != 0)
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'service_type' => 'Lab', 'status' => 'Sale', 'amount' => 50, 'net_amount' => 40
        ]);
        // Full Discount (net_amount = 0)
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'IP',
            'service_type' => 'Lab', 'status' => 'Sale', 'amount' => 100, 'net_amount' => 0
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        $this->assertEquals(50.0, $report['discount']['ftd']['partial']['op']);
        $this->assertEquals(100.0, $report['discount']['ftd']['full']['ip']);
    }

    public function test_calculates_refunds_correctly()
    {
        $branch = Branch::CHROMEPET;
        $date = '2026-08-21';

        // Refund (amount can be negative in DB, should be absolute in report)
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'ER',
            'service_type' => 'Procedure', 'status' => 'Refund', 'amount' => -150, 'net_amount' => -150
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        $this->assertEquals(150.0, $report['refund']['ftd']['er']);
    }

    public function test_calculates_mri_stats_correctly()
    {
        $branch = Branch::CHROMEPET; // MRI only applies to Chromepet based on emptyMri for Oragadam
        $date = '2026-08-21';

        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'sub_department' => 'MRI', 'quantity' => 2, 'net_amount' => 8000
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        $this->assertEquals(2, $report['mri']['ftd']['op']['count']);
        $this->assertEquals(8000.0, $report['mri']['ftd']['op']['revenue']);
    }

    public function test_calculates_collections_correctly()
    {
        $branch = Branch::ORAGADAM;
        $date = '2026-08-21';

        CashierCollection::factory()->create([
            'branch' => $branch->value, 'collection_date' => $date, 'patient_type' => null, // Pharmacy
            'paid_amount' => 300
        ]);
        CashierCollection::factory()->create([
            'branch' => $branch->value, 'collection_date' => $date, 'patient_type' => 'OP',
            'paid_amount' => 400
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        $this->assertEquals(300.0, $report['collection']['ftd']['ph']);
        $this->assertEquals(400.0, $report['collection']['ftd']['op']);
    }

    public function test_applies_branch_adjustments_for_chromepet()
    {
        $branch = Branch::CHROMEPET;
        $date = '2026-08-21';

        PackageConsumption::factory()->create([
            'branch' => $branch->value, 'consumption_date' => $date, 'amount' => 1000
        ]);

        // Base sales
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'IP',
            'service_type' => 'Procedure', 'status' => 'Sale', 'amount' => 5000, 'net_amount' => 5000
        ]);
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'service_type' => 'Pharmacy', 'status' => 'Sale', 'amount' => 200, 'net_amount' => 200
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        // IP Sales = 5000 - 1000 = 4000
        $this->assertEquals(4000.0, $report['sales']['ftd']['ip']);
        // PH Sales = 200 + 1000 = 1200
        $this->assertEquals(1200.0, $report['sales']['ftd']['ph']);
        $this->assertEquals(1000.0, $report['pkg_adjustment']['ftd']);
    }

    public function test_ignores_branch_adjustments_for_oragadam()
    {
        $branch = Branch::ORAGADAM;
        $date = '2026-08-21';

        PackageConsumption::factory()->create([
            'branch' => $branch->value, 'consumption_date' => $date, 'amount' => 1000
        ]);

        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'IP',
            'service_type' => 'Procedure', 'status' => 'Sale', 'amount' => 5000, 'net_amount' => 5000
        ]);

        $report = $this->service->generateMIS($branch, $date, []);

        // IP Sales should remain 5000
        $this->assertEquals(5000.0, $report['sales']['ftd']['ip']);
        $this->assertEquals(0.0, $report['pkg_adjustment']['ftd']);
    }
    
    public function test_calculates_volume_payload_with_existing_mtd_data()
    {
        $branch = Branch::CHROMEPET;
        $date = '2026-08-21';
        
        // MTD from previous days (e.g. 20th)
        MisReport::factory()->create([
            'branch' => $branch->value, 'report_date' => '2026-08-20',
            'occupancy' => 50, 'occupancy_pct' => 60.5, 'admission' => 10, 'discharge' => 8, 'er_count' => 5
        ]);
        MisReport::factory()->create([
            'branch' => $branch->value, 'report_date' => '2026-08-19',
            'occupancy' => 45, 'occupancy_pct' => 55.0, 'admission' => 12, 'discharge' => 10, 'er_count' => 3
        ]);
        
        // FTD OP Consultation count
        BillItem::factory()->create([
            'branch' => $branch->value, 'bill_date' => $date, 'patient_type' => 'OP',
            'service_type' => 'OP Consultation', 'quantity' => 20
        ]);
        
        $volumeDataInput = [
            'ftd' => [
                'occupancy' => 60,
                'occupancy_pct' => 80.0,
                'admission' => 15,
                'discharge' => 10,
                'er_count' => 8
            ]
        ];

        $report = $this->service->generateMIS($branch, $date, $volumeDataInput);

        // FTD asserts
        $this->assertEquals(60, $report['volume']['ftd']['occupancy']);
        $this->assertEquals(20, $report['volume']['ftd']['total_op']);
        
        // MTD asserts (prev days + today)
        // Occupancy: 50 + 45 + 60 = 155
        $this->assertEquals(155, $report['volume']['mtd']['occupancy']);
        
        // Admission: 10 + 12 + 15 = 37
        $this->assertEquals(37, $report['volume']['mtd']['admission']);
        
        // ER: 5 + 3 + 8 = 16
        $this->assertEquals(16, $report['volume']['mtd']['er_count']);
        
        // Occupancy Pct avg: (60.5 + 55.0 + 80.0) / 3 = 195.5 / 3 = 65.166 => rounded to 65
        $this->assertEquals(65, $report['volume']['mtd']['occupancy_pct']);
    }
}
