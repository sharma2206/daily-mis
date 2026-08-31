<?php

namespace Tests\Feature\MIS;

use App\Enums\Branch;
use App\Models\MisReport;
use App\Services\CsvProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\MockInterface;
use Tests\TestCase;

class MISControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_summary_for_all_branches()
    {
        $date = '2026-08-21';

        MisReport::factory()->create([
            'branch' => Branch::CHROMEPET->value,
            'report_date' => $date,
            'report_data' => ['sales' => ['ftd' => ['ph' => 100]]]
        ]);

        $response = $this->getJson("/api/mis/dashboard/{$date}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         Branch::CHROMEPET->value => ['branch', 'branch_key', 'bed_count', 'has_data', 'report'],
                         Branch::ORAGADAM->value => ['branch', 'branch_key', 'bed_count', 'has_data', 'report'],
                     ]
                 ]);

        $this->assertTrue($response->json('data.chromepet.has_data'));
        $this->assertFalse($response->json('data.oragadam.has_data'));
    }

    public function test_show_returns_mis_report_for_valid_branch_and_date()
    {
        $date = '2026-08-21';
        $branch = Branch::ORAGADAM->value;

        $response = $this->getJson("/api/mis/{$branch}/{$date}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'branch', 'branch_key', 'date', 'sales', 'collection', 'discount', 'refund', 'volume', 'generated_at', 'totals'
                     ]
                 ]);
                 
        $this->assertEquals($branch, $response->json('data.branch_key'));
        $this->assertEquals($date, $response->json('data.date'));
    }

    public function test_rejects_invalid_branch_in_routes()
    {
        $date = '2026-08-21';
        $response = $this->getJson("/api/mis/invalid-branch/{$date}");
        
        $response->assertStatus(404); // Route constraint fails
    }

    public function test_rejects_invalid_date_format_in_routes()
    {
        $branch = Branch::CHROMEPET->value;
        $response = $this->getJson("/api/mis/{$branch}/2026-08-21T10:00:00");
        
        $response->assertStatus(404); // Route constraint fails
    }

    public function test_upload_processes_files_and_returns_generated_report()
    {
        Storage::fake('local');
        $date = '2026-08-21';
        $branch = Branch::CHROMEPET->value;

        $billFile = UploadedFile::fake()->create('bill.csv', 100);
        $cashierFile = UploadedFile::fake()->create('cashier.csv', 100);
        $packageFile = UploadedFile::fake()->create('package.csv', 100);

        $this->mock(CsvProcessingService::class, function (MockInterface $mock) use ($date) {
            $mock->shouldReceive('process')
                 ->once()
                 ->andReturn(['bill_items' => 10, 'collections' => 5, 'packages' => 2]);
        });

        $payload = [
            'date' => $date,
            'bill_file' => $billFile,
            'cashier_file' => $cashierFile,
            'package_file' => $packageFile,
            'occupancy' => 50,
            'occupancy_pct' => 70,
            'admission' => 10,
            'discharge' => 5,
            'er_count' => 20
        ];

        $response = $this->postJson("/api/mis/{$branch}/upload", $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'imported' => ['bill_items', 'collections', 'packages'],
                     'data' => [
                         'branch', 'branch_key', 'date', 'volume'
                     ]
                 ]);
                 
        $this->assertEquals(50, $response->json('data.volume.ftd.occupancy'));
    }

    public function test_export_triggers_excel_download()
    {
        Excel::fake();
        
        $date = '2026-08-21';
        $branch = Branch::CHROMEPET->value;
        
        $response = $this->get("/api/mis/{$branch}/{$date}/export");

        $response->assertStatus(200);
        
        Excel::assertDownloaded("Sales VS Collection 21st August 2026(Chromepet).xlsx");
    }
}
