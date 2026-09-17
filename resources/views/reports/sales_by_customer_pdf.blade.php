<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan per Pelanggan</title>
    <style>
        @page {
            margin: 18px 20px 22px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #ea580c;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 8.5px;
            font-style: italic;
            color: #64748b;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            padding: 6px 4px;
            border: 1px solid #334155;
            text-align: center;
        }
        .data-table td {
            padding: 5px 4px;
            font-size: 8.5px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .summary-row td {
            background-color: #f1f5f9 !important;
            font-weight: bold;
            font-size: 9px;
            color: #0f172a;
            border-top: 1px solid #94a3b8 !important;
            border-bottom: 3px double #0f172a !important;
            padding: 6px 4px;
        }
        .footer-note {
            margin-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>
    @php
        $companyName = \App\Models\Setting::get('company_name', \App\Models\Setting::get('app_name', 'POS Retail Pro'));
    @endphp

    <div class="kop-container">
        <div class="company-name">{{ strtoupper($companyName) }}</div>
        <div class="report-title">LAPORAN PENJUALAN PER PELANGGAN (CUSTOMER)</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="28%" class="text-left">Nama Pelanggan</th>
                <th width="16%" class="text-center">No. Telepon / Kode</th>
                <th width="12%" class="text-center">Jumlah Transaksi</th>
                <th width="15%" class="text-right">Total Belanja</th>
                <th width="13%" class="text-right">Rata-rata Order</th>
                <th width="12%" class="text-center">Order Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumOrders = 0;
                $sumSpent = 0;
            @endphp
            @forelse($customers as $index => $c)
            @php
                $orders = (int) $c->total_orders;
                $spent = (float) $c->total_spent;
                $avg = (float) $c->avg_spent;

                $sumOrders += $orders;
                $sumSpent += $spent;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $c->customer_name ?? 'Pelanggan Umum' }}</td>
                <td class="text-center text-muted">{{ $c->customer_phone ?? ($c->customer_code ?? '-') }}</td>
                <td class="text-center font-bold" style="color: #ea580c;">{{ $orders }} Transaksi</td>
                <td class="text-right font-bold">Rp {{ number_format($spent, 0, ',', '.') }}</td>
                <td class="text-right text-muted">Rp {{ number_format($avg, 0, ',', '.') }}</td>
                <td class="text-center text-muted">{{ $c->last_order_date ? \Carbon\Carbon::parse($c->last_order_date)->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada transaksi pelanggan ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse

            @if(count($customers) > 0)
            @php
                $overallAvg = $sumOrders > 0 ? ($sumSpent / $sumOrders) : 0;
            @endphp
            <tr class="summary-row">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ number_format($sumOrders, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumSpent, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($overallAvg, 0, ',', '.') }}</td>
                <td class="text-center">-</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh Sistem POS & Inventori.</div>
        <div class="footer-right">Halaman 1 dari 1</div>
    </div>
</body>
</html>
