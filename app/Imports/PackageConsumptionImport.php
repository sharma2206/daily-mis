<?php

namespace App\Imports;

use App\Enums\Branch;
use App\Models\PackageConsumption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PackageConsumptionImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public int $rowCount = 0;

    public function __construct(
        private Branch $branch,
        private string $date
    ) {}

    public function collection(Collection $rows): void
    {
        $insert = [];

        foreach ($rows as $index => $row) {
            // Debug first row INSIDE the loop
            if ($index === 0 && $this->rowCount === 0) {
                Log::info('First row keys:', array_keys($row->toArray()));
                Log::info('First row data:', $row->toArray());
            }

            $serviceType = strtolower(trim($row['package_service_type'] ?? ''));
            if ($serviceType !== 'pharmacy') {
                continue;
            }

            // Clean and convert amount
            $amount = str_replace(',', '', $row['service_item_amount'] ?? '0');
            $amount = (float) trim($amount);

            $insert[] = [
                'branch'           => $this->branch->value,
                'consumption_date' => $this->date,
                'amount'           => round($amount, 2), // Convert to paise
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (!empty($insert)) {
            // Use chunking for large inserts
            foreach (array_chunk($insert, 500) as $chunk) {
                PackageConsumption::insert($chunk);
            }
            $this->rowCount += count($insert);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
