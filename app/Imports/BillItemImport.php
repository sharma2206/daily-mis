<?php

namespace App\Imports;

use App\Enums\Branch;
use App\Models\BillItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BillItemImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * Track total rows imported.
     */
    public int $rowCount = 0;

    /**
     * @param Branch $branch
     */
    public function __construct(
        private Branch $branch,
    ) {}

    /**
     * Process each chunk of rows from the CSV.
     */
    public function collection(Collection $rows): void
    {
        $insert = [];

        foreach ($rows as $row) {
            $patientId = trim((string) $this->getValue(
                $row,
                ['patient_id', 'uhid'],
                ''
            ));

            $billNo = trim((string) $this->getValue(
                $row,
                ['bill_no', 'bill number'],
                ''
            ));

            if ($patientId === '' && $billNo === '') {
                continue;
            }

            /*
             * ---------------------------------------------------------
             * AMOUNT
             * CSV Header: Amount
             * ---------------------------------------------------------
             */
            $amount = $this->toFloat(
                $this->getValue(
                    $row,
                    [
                        'amount',
                        'amt',
                        'total_amount',
                    ],
                    0
                )
            );

            /*
             * ---------------------------------------------------------
             * NET AMOUNT
             * CSV Header: Net Amt
             *
             * IMPORTANT:
             * Use Net Amt directly from CSV.
             * Do not calculate it from Amount - Discount if Net Amt
             * already exists.
             * ---------------------------------------------------------
             */
            $netAmountRaw = $this->getValue(
                $row,
                [
                    'net_amt',
                    'net amount',
                    'net_amount',
                    'netamount',
                ],
                null
            );

            /*
             * CSV Header: Discount Amt
             */
            $discountRaw = $this->getValue(
                $row,
                [
                    'discount_amt',
                    'discount amount',
                    'discount',
                    'disc',
                    'discount_amount',
                ],
                null
            );

            if ($netAmountRaw !== null && trim((string) $netAmountRaw) !== '') {

                /*
                 * Source of truth:
                 * CSV Net Amt
                 */
                $netAmount = $this->toFloat($netAmountRaw);
            } elseif ($discountRaw !== null && trim((string) $discountRaw) !== '') {

                /*
                 * Fallback only when Net Amt is genuinely missing.
                 */
                $discount = $this->toFloat($discountRaw);

                $netAmount = $amount - $discount;
            } else {

                /*
                 * Last fallback.
                 */
                $netAmount = $amount;
            }

            /*
             * ---------------------------------------------------------
             * BILL DATE
             *
             * CSV Header:
             * Bill / Refund Creation Date Time
             *
             * DO NOT use $this->date here.
             * ---------------------------------------------------------
             */
            $billCreationDateTime = $this->getValue(
                $row,
                [
                    'bill_refund_creation_date_time',
                    'bill / refund creation date time',
                    'bill/refund creation date time',
                    'bill refund creation date time',
                ],
                null
            );

            if (
                $billCreationDateTime === null ||
                trim((string) $billCreationDateTime) === ''
            ) {

                throw new \RuntimeException(
                    'Bill / Refund Creation Date Time is missing for Bill No: '
                        . ($billNo ?: 'Unknown')
                );
            }

            $billDate = $this->parseBillDate($billCreationDateTime);

            /*
             * ---------------------------------------------------------
             * OTHER FIELDS
             * ---------------------------------------------------------
             */
            $patientType = $this->getValue(
                $row,
                [
                    'patient_type',
                    'patient type',
                ],
                null
            );

            $serviceType = $this->getValue(
                $row,
                [
                    'service_type',
                    'service type',
                ],
                ''
            );

            $subDepartment = $this->getValue(
                $row,
                [
                    'sub_department',
                    'sub-department',
                    'sub department',
                ],
                null
            );

            $status = $this->getValue(
                $row,
                [
                    'status',
                ],
                'Active'
            );

            $quantity = $this->getValue(
                $row,
                [
                    'quantity',
                    'qty',
                ],
                1
            );

            /*
             * ---------------------------------------------------------
             * INSERT
             * ---------------------------------------------------------
             */
            $insert[] = [
                'branch'         => $this->branch->value,

                /*
                 * Actual date from CSV row.
                 */
                'bill_date'      => $billDate,

                'patient_id'     => $patientId,

                'patient_type'   => $this->normalizePatientType(
                    $patientType !== null
                        ? trim((string) $patientType)
                        : null
                ),

                'service_type'   => trim((string) $serviceType),

                'sub_department' => $subDepartment !== null
                    ? (trim((string) $subDepartment) ?: null)
                    : null,

                'amount'         => $amount,

                /*
                 * Directly from CSV Net Amt.
                 */
                'net_amount'     => $netAmount,

                'quantity'       => (int) $quantity,

                'status'         => trim((string) $status) ?: 'Active',

                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        /*
         * Bulk insert in chunks.
         */
        if (!empty($insert)) {

            foreach (array_chunk($insert, 500) as $chunk) {
                BillItem::insert($chunk);
            }

            $this->rowCount += count($insert);
        }
    }

    /**
     * Get value using multiple possible CSV header names.
     *
     * This normalization removes:
     * - spaces
     * - underscores
     * - hyphens
     * - slash
     * - other special characters
     *
     * Therefore:
     *
     * "Net Amt"
     * "net_amt"
     * "Net-Amt"
     * "net amount"
     *
     * all resolve to:
     *
     * netamt
     */
    private function getValue(
        $row,
        array $keys,
        $default = 0
    ) {
        if ($row instanceof Collection) {
            $row = $row->all();
        }

        if (!is_array($row)) {
            return $default;
        }

        /*
         * First try exact keys.
         */
        foreach ($keys as $key) {

            if (
                array_key_exists($key, $row) &&
                $this->hasValue($row[$key])
            ) {
                return $row[$key];
            }
        }

        /*
         * Build normalized row keys.
         */
        $normalized = [];

        foreach ($row as $rowKey => $rowValue) {

            $normalizedKey = $this->normalizeHeader($rowKey);

            $normalized[$normalizedKey] = $rowValue;
        }

        /*
         * Try normalized keys.
         */
        foreach ($keys as $key) {

            $normalizedKey = $this->normalizeHeader($key);

            if (
                array_key_exists($normalizedKey, $normalized) &&
                $this->hasValue($normalized[$normalizedKey])
            ) {
                return $normalized[$normalizedKey];
            }
        }

        return $default;
    }

    /**
     * Normalize CSV heading.
     */
    private function normalizeHeader($key): string
    {
        return strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                trim((string) $key)
            )
        );
    }

    /**
     * Check whether a value is non-empty.
     */
    private function hasValue($value): bool
    {
        return $value !== null &&
            trim((string) $value) !== '';
    }

    /**
     * Convert amount values safely.
     */
    private function toFloat($value): float
    {
        if ($value === null || trim((string) $value) === '') {
            return 0.0;
        }

        /*
         * Remove commas and currency symbols if present.
         */
        $value = str_replace(
            [',', '₹'],
            '',
            trim((string) $value)
        );

        return (float) $value;
    }

    /**
     * Parse:
     *
     * 09/08/2026, 10:47 pm
     * 10/08/2026, 11:20 pm
     *
     * and return:
     *
     * 2026-08-09
     * 2026-08-10
     */
    private function parseBillDate($value): string
    {
        $value = trim((string) $value);

        $formats = [
            'd/m/Y, h:i a',
            'd/m/Y, H:i',
            'd/m/Y h:i a',
            'd/m/Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
        ];

        foreach ($formats as $format) {

            try {

                $date = Carbon::createFromFormat(
                    $format,
                    $value
                );

                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        /*
         * Final fallback using Carbon parser.
         */
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {

            throw new \RuntimeException(
                "Invalid Bill / Refund Creation Date Time: {$value}"
            );
        }
    }

    /**
     * Chunk size.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Normalize patient type.
     */
    private function normalizePatientType(?string $type): ?string
    {
        if (!$type || trim($type) === '') {
            return null;
        }

        $t = strtoupper(trim($type));

        return match (true) {
            str_contains($t, 'OP') => 'OP',
            str_contains($t, 'IP'),
            str_contains($t, 'INPATIENT') => 'IP',
            str_contains($t, 'ER'),
            str_contains($t, 'EMERGENCY') => 'ER',

            default => $t,
        };
    }
}
