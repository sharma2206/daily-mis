<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MISExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    private array $data;
    private bool $isChromepet;
    private bool $isOragadam;

    public function __construct(array $data)
    {
        $this->data = $data;
        $branchKey = strtolower($data['branch_key'] ?? $data['branch'] ?? '');
        $this->isChromepet = $branchKey === 'chromepet';
        $this->isOragadam = $branchKey === 'oragadam';
    }

    /**
     * Helper to convert value to Lakhs with 2 decimals and thousand separators (PDF format)
     */
    private function lakhs($value): string
    {
        return number_format(($value ?? 0) / 100000, 2);
    }

    /**
     * Helper to format percentage value
     */
    private function percentage($value): string
    {
        return number_format($value ?? 0, 0) . '%';
    }

    /**
     * Helper to format plain number with 2 decimals
     */
    private function number($value): string
    {
        return number_format($value ?? 0, 2);
    }

    /**
     * Helper to format currency value with rupee symbol and 2 decimals
     */
    private function currency($value): string
    {
        return '₹ ' . number_format($value ?? 0, 2);
    }

    /**
     * Get the revenue column keys for the current branch.
     * Chromepet: OP, IP, PH (no ER)
     * Oragadam: OP, IP, ER, PH
     */
    private function revKeys(): array
    {
        return $this->isChromepet ? ['op', 'ip', 'ph'] : ['op', 'ip', 'er', 'ph'];
    }

    /**
     * Build a revenue row array with branch-specific columns.
     */
    private function buildRevenueRow(string $label, array $ftd, array $mtd): array
    {
        $keys = $this->revKeys();
        $row = [$label];
        $ftdTotal = 0;
        foreach ($keys as $k) {
            $val = $ftd[$k] ?? 0;
            $row[] = $this->lakhs($val);
            $ftdTotal += $val;
        }
        $row[] = $this->lakhs($ftdTotal);
        $mtdTotal = 0;
        foreach ($keys as $k) {
            $val = $mtd[$k] ?? 0;
            $row[] = $this->lakhs($val);
            $mtdTotal += $val;
        }
        $row[] = $this->lakhs($mtdTotal);
        return $row;
    }

    /**
     * Pad row to fixed column count for alignment.
     */
    private function padRow(array $row, int $cols): array
    {
        while (count($row) < $cols) {
            $row[] = "";
        }
        return $row;
    }

    public function headings(): array
    {
        $date = $this->data['date'] ?? '';
        $keys = $this->revKeys();
        $colCount = count($keys) * 2 + 3; // label + (keys+total) * 2

        // Row 1: Title
        $row1 = array_pad(["MIS - DATE {$date}"], $colCount, "");
        // Row 2: FTD/MTD groups
        $row2 = array_pad(["REVENUE", "FTD"], $colCount, "");
        $ftdEndIdx = count($keys) + 1; // after label + keys count
        $row2[$ftdEndIdx] = "MTD";
        // Row 3: Column headers
        $row3 = [""];
        foreach ($keys as $k) {
            $row3[] = strtoupper($k);
        }
        $row3[] = "Total";
        foreach ($keys as $k) {
            $row3[] = strtoupper($k);
        }
        $row3[] = "Total";

        return [$row1, $row2, $row3];
    }

    public function array(): array
    {
        $sales  = $this->data['sales'] ?? [];
        $col    = $this->data['collection'] ?? [];
        $disc   = $this->data['discount'] ?? [];
        $ref    = $this->data['refund'] ?? [];
        $vol    = $this->data['volume'] ?? [];
        $mri    = $this->data['mri'] ?? [];
        $totalCols = count($this->revKeys()) * 2 + 3;

        $rows = [];

        // Revenue rows
        $rows[] = $this->buildRevenueRow("Sales", $sales['ftd'] ?? [], $sales['mtd'] ?? []);
        $rows[] = $this->buildRevenueRow("Collection", $col['ftd'] ?? [], $col['mtd'] ?? []);
        $rows[] = $this->buildRevenueRow("Discount 99%", $disc['ftd']['partial'] ?? [], $disc['mtd']['partial'] ?? []);
        $rows[] = $this->buildRevenueRow("Discount 100%", $disc['ftd']['full'] ?? [], $disc['mtd']['full'] ?? []);
        $rows[] = $this->buildRevenueRow("Refund", $ref['ftd'] ?? [], $ref['mtd'] ?? []);

        // Blank separator
        $rows[] = $this->padRow([""], $totalCols);
        // Volume sub-header
        $rows[] = $this->padRow(["", "FTD", "MTD"], $totalCols);
        $rows[] = $this->padRow(["Volume Indicators"], $totalCols);
        $rows[] = $this->padRow(["Occupancy", $this->number($vol['ftd']['occupancy'] ?? 0), $this->number($vol['mtd']['occupancy'] ?? 0)], $totalCols);
        $rows[] = $this->padRow(["Occupancy %", $this->percentage($vol['ftd']['occupancy_pct'] ?? 0), $this->percentage($vol['mtd']['occupancy_pct'] ?? 0)], $totalCols);
        $rows[] = $this->padRow(["Admission", $this->number($vol['ftd']['admission'] ?? 0), $this->number($vol['mtd']['admission'] ?? 0)], $totalCols);
        $rows[] = $this->padRow(["Discharge", $this->number($vol['ftd']['discharge'] ?? 0), $this->number($vol['mtd']['discharge'] ?? 0)], $totalCols);
        $rows[] = $this->padRow(["Total OP", $this->number($vol['ftd']['total_op'] ?? 0), $this->number($vol['mtd']['total_op'] ?? 0)], $totalCols);

        // Oragadam: Total ER
        if ($this->isOragadam) {
            $rows[] = $this->padRow(["Total ER", $this->number($vol['ftd']['er_count'] ?? 0), $this->number($vol['mtd']['er_count'] ?? 0)], $totalCols);
        }

        // Chromepet: MRI data
        if ($this->isChromepet) {
            $rows[] = $this->padRow(["MRI OP (count)", $this->number($mri['ftd']['op']['count'] ?? 0), $this->number($mri['mtd']['op']['count'] ?? 0)], $totalCols);
            $rows[] = $this->padRow(["MRI IP (count)", $this->number($mri['ftd']['ip']['count'] ?? 0), $this->number($mri['mtd']['ip']['count'] ?? 0)], $totalCols);
            $rows[] = $this->padRow(["Revenue"], $totalCols);
            $rows[] = $this->padRow(["MRI OP (₹)", $this->currency($mri['ftd']['op']['revenue'] ?? 0), $this->currency($mri['mtd']['op']['revenue'] ?? 0)], $totalCols);
            $rows[] = $this->padRow(["MRI IP (₹)", $this->currency($mri['ftd']['ip']['revenue'] ?? 0), $this->currency($mri['mtd']['ip']['revenue'] ?? 0)], $totalCols);
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 12,
        ];
    }

    public function title(): string
    {
        $branch = ucfirst($this->data['branch'] ?? '');
        $date = $this->data['date'] ?? '';
        return substr("MIS {$branch} {$date}", 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $keys = $this->revKeys();
        $colCount = count($keys) * 2 + 3; // Total columns in the sheet
        $lastCol = chr(ord('A') + $colCount - 1); // Last column letter
        $totalFtdCol = chr(ord('A') + count($keys) + 1); // FTD Total column
        $totalMtdCol = $lastCol; // MTD Total column

        // Row 1: Merged header
        $sheet->mergeCells("A1:{$lastCol}1");
        // Row 2: Column groups - FTD and MTD
        $ftdStart = 'B';
        $ftdEnd = chr(ord('A') + count($keys) + 1);
        $mtdStart = chr(ord($ftdEnd) + 1);
        $sheet->mergeCells("{$ftdStart}2:{$ftdEnd}2");
        $sheet->mergeCells("{$mtdStart}2:{$lastCol}2");

        // Volume Indicators header
        $volHeaderRow = 11; // row 3 (heading) + 5 data rows + 1 blank + 1 sub-header + 1 = row 11
        $sheet->mergeCells("A{$volHeaderRow}:C{$volHeaderRow}");

        // Revenue header row for Chromepet MRI
        if ($this->isChromepet) {
            $mriRevenueRow = $volHeaderRow + 8; // After vol indicators + MRI count rows
            $sheet->mergeCells("A{$mriRevenueRow}:C{$mriRevenueRow}");
        }

        // Alternating row background: light gray
        $lightGray = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']];
        foreach ([5, 7] as $row) {
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->applyFromArray($lightGray);
        }

        // Set text alignment for formatted values
        $sheet->getStyle("B4:{$lastCol}8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DDEBF7'], // Light blue
                ],
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'], // Gray
                ],
            ],
            3 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'], // Gray
                ],
            ],
            $totalFtdCol => ['font' => ['bold' => true]], // Grand Total FTD
            $totalMtdCol => ['font' => ['bold' => true]], // Grand Total MTD
            'A' => ['font' => ['bold' => true]], // Row Labels
            10 => ['font' => ['bold' => true]], // Sub-header FTD MTD
            $volHeaderRow => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ];
    }
}
