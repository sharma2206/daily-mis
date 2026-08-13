<?php

namespace App\Services;

use App\Enums\Branch;
use App\Imports\BillItemImport;
use App\Imports\CashierCollectionImport;
use App\Imports\PackageConsumptionImport;
use App\Models\BillItem;
use App\Models\CashierCollection;
use App\Models\PackageConsumption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CsvProcessingService
{
    /**
     * Process uploaded CSV files for a given branch and date.
     *
     * Chromepet: bill_file + cashier_file + package_file (3 files)
     * Oragadam:  bill_file + cashier_file               (2 files)
     *
     * @param Branch $branch
     * @param string $date
     * @param UploadedFile $billFile
     * @param UploadedFile $cashierFile
     * @param UploadedFile|null $packageFile
     * @return array Row counts per file type
     */
    public function process(
        Branch $branch,
        string $date,
        UploadedFile $billFile,
        UploadedFile $cashierFile,
        ?UploadedFile $packageFile = null
    ): array {
        return DB::transaction(function () use ($branch, $date, $billFile, $cashierFile, $packageFile) {

            // 1. Wipe existing data for this branch + date to prevent duplicates
            $this->deleteExisting($branch, $date);

            // 2. Import Bill Items
            $billImport = new BillItemImport($branch, $date);
            Excel::import($billImport, $billFile);

            // 3. Import Cashier Collections
            $cashierImport = new CashierCollectionImport($branch, $date);
            Excel::import($cashierImport, $cashierFile);

            // 4. Import Package Consumptions (Chromepet only)
            $packageCount = 0;
            if ($branch === Branch::CHROMEPET && $packageFile) {
                $packageImport = new PackageConsumptionImport($branch, $date);
                Excel::import($packageImport, $packageFile);
                $packageCount = $packageImport->rowCount;
            }

            return [
                'bill_items'           => $billImport->rowCount,
                'cashier_collections'  => $cashierImport->rowCount,
                'package_consumptions' => $packageCount,
            ];
        });
    }

    /**
     * Delete existing records for a branch + date to prevent duplicate imports.
     *
     * @param Branch $branch
     * @param string $date
     * @return void
     */
    private function deleteExisting(Branch $branch, string $date): void
    {
        BillItem::where('branch', $branch->value)
            ->whereDate('bill_date', $date)
            ->delete();

        CashierCollection::where('branch', $branch->value)
            ->whereDate('collection_date', $date)
            ->delete();

        if ($branch === Branch::CHROMEPET) {
            PackageConsumption::where('branch', $branch->value)
                ->whereDate('consumption_date', $date)
                ->delete();
        }
    }
}
