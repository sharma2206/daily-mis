<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>MIS Report - {{ $data['branch'] ?? '' }} - {{ $data['date'] ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: auto%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        th {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .revenue-table {
            width: 80%;
            margin: 0 auto 20px auto;
        }

        .volume-table {
            width: 35%;
            margin: 0 auto 0 10%;
        }

        .header-title {
            font-size: 12px;
            font-weight: bold;
            padding: 10px;
        }

        .row-header {
            text-align: left;
            font-weight: bold;
            background-color: #fff;
        }

        .section-header {
            background-color: #e0e0e0;
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        $sales = $data['sales'] ?? [];
        $col = $data['collection'] ?? [];
        $disc = $data['discount'] ?? [];
        $ref = $data['refund'] ?? [];
        $vol = $data['volume'] ?? [];
        $mri = $data['mri'] ?? [];

        $lk = fn($v) => number_format(($v ?? 0) / 100000, 2);
        $pct = fn($v) => number_format($v ?? 0, 0) . '%';
        $num = fn($v) => number_format($v ?? 0, 0, '', '');

        $formattedDate = isset($data['date']) ? date('d/m/Y', strtotime($data['date'])) : '';
        $branchKey = strtolower($data['branch_key'] ?? ($data['branch'] ?? ''));
        $isChromepet = $branchKey === 'chromepet';
        $isOragadam = $branchKey === 'oragadam';
        // Both branches: OP, IP, ER, PH = 4 cols + Total = 5 colspan
        $revColspan = 5;
        $totalColspan = 11;
    @endphp

    <!-- Revenue Table -->
    <table class="revenue-table">
        <thead>
            <tr>
                <th colspan="{{ $totalColspan }}" class="header-title">MIS - DATE {{ $formattedDate }}</th>
            </tr>
            <tr>
                <th rowspan="2" class="row-header">REVENUE</th>
                <th colspan="{{ $revColspan }}">FTD</th>
                <th colspan="{{ $revColspan }}">MTD</th>
            </tr>
            <tr>
                <th>OP</th>
                <th>IP</th>
                <th>ER</th>
                <th>PH</th>
                <th>Total</th>
                <th>OP</th>
                <th>IP</th>
                <th>ER</th>
                <th>PH</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $revenueRows = [
                    ['label' => 'Sales', 'ftd' => $sales['ftd'] ?? [], 'mtd' => $sales['mtd'] ?? []],
                    ['label' => 'Collection', 'ftd' => $col['ftd'] ?? [], 'mtd' => $col['mtd'] ?? []],
                    [
                        'label' => 'Discount 99%',
                        'ftd' => $disc['ftd']['partial'] ?? [],
                        'mtd' => $disc['mtd']['partial'] ?? [],
                    ],
                    [
                        'label' => 'Discount 100%',
                        'ftd' => $disc['ftd']['full'] ?? [],
                        'mtd' => $disc['mtd']['full'] ?? [],
                    ],
                    ['label' => 'Refund', 'ftd' => $ref['ftd'] ?? [], 'mtd' => $ref['mtd'] ?? []],
                ];
                $revKeys = ['op', 'ip', 'er', 'ph'];
            @endphp
            @foreach ($revenueRows as $row)
                <tr>
                    <td class="row-header">{{ $row['label'] }}</td>
                    @foreach ($revKeys as $key)
                        <td>{{ $lk($row['ftd'][$key] ?? 0) }}</td>
                    @endforeach
                    <td>{{ $lk(collect($revKeys)->sum(fn($k) => $row['ftd'][$k] ?? 0)) }}</td>
                    @foreach ($revKeys as $key)
                        <td>{{ $lk($row['mtd'][$key] ?? 0) }}</td>
                    @endforeach
                    <td>{{ $lk(collect($revKeys)->sum(fn($k) => $row['mtd'][$k] ?? 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Volume Indicators Table -->
    <table class="volume-table">
        <thead>
            <tr>
                <th class="row-header" style="width: 50%;"></th>
                <th>FTD</th>
                <th>MTD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" class="section-header">Volume indicators</td>
            </tr>
            <tr>
                <td class="row-header">Occupancy</td>
                <td>{{ $num($vol['ftd']['occupancy'] ?? 0) }}</td>
                <td>{{ $num($vol['mtd']['occupancy'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="row-header">Occupancy %</td>
                <td>{{ $pct($vol['ftd']['occupancy_pct'] ?? 0) }}</td>
                <td>{{ $pct($vol['mtd']['occupancy_pct'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="row-header">Admission</td>
                <td>{{ $num($vol['ftd']['admission'] ?? 0) }}</td>
                <td>{{ $num($vol['mtd']['admission'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="row-header">Discharge</td>
                <td>{{ $num($vol['ftd']['discharge'] ?? 0) }}</td>
                <td>{{ $num($vol['mtd']['discharge'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="row-header">Total OP</td>
                <td>{{ $num($vol['ftd']['total_op'] ?? 0) }}</td>
                <td>{{ $num($vol['mtd']['total_op'] ?? 0) }}</td>
            </tr>
            @if ($isOragadam)
                <tr>
                    <td class="row-header">Total ER</td>
                    <td>{{ $num($vol['ftd']['er_count'] ?? 0) }}</td>
                    <td>{{ $num($vol['mtd']['er_count'] ?? 0) }}</td>
                </tr>
            @endif
            @if ($isChromepet)
                <tr>
                    <td class="row-header">MRI OP</td>
                    <td>{{ $num($mri['ftd']['op']['count'] ?? 0) }}</td>
                    <td>{{ $num($mri['mtd']['op']['count'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="row-header">MRI IP</td>
                    <td>{{ $num($mri['ftd']['ip']['count'] ?? 0) }}</td>
                    <td>{{ $num($mri['mtd']['ip']['count'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="section-header">Revenue</td>
                </tr>
                <tr>
                    <td class="row-header">MRI OP</td>
                    <td>₹ {{ number_format($mri['ftd']['op']['revenue'] ?? 0, 0, '', '') }}</td>
                    <td>₹ {{ number_format($mri['mtd']['op']['revenue'] ?? 0, 0, '', '') }}</td>
                </tr>
                <tr>
                    <td class="row-header">MRI IP</td>
                    <td>₹ {{ number_format($mri['ftd']['ip']['revenue'] ?? 0, 0, '', '') }}</td>
                    <td>₹ {{ number_format($mri['mtd']['ip']['revenue'] ?? 0, 0, '', '') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>

</html>
