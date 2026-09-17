<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan per Kategori</title>
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
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-high { background-color: #dcfce7; color: #15803d; }
        .badge-mid { background-color: #fef3c7; color: #b45309; }
        .badge-low { background-color: #fee2e2; color: #b91c1c; }
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
        <div class="report-title">LAPORAN PENJUALAN PER KATEGORI PRODUK</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Cabang/Gudang: {{ $warehouse->name ?? 'Semua Cabang / Gudang' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="30%" class="text-left">Nama Kategori</th>
                <th width="12%" class="text-center">Variasi Produk</th>
                <th width="12%" class="text-center">Total Qty Terjual</th>
                <th width="14%" class="text-right">Total Pendapatan</th>
                <th width="14%" class="text-right">Total HPP Modal</th>
                <th width="14%" class="text-right">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumQty = 0;
                $sumRevenue = 0;
                $sumCost = 0;
                $sumProfit = 0;
            @endphp
            @forelse($categoriesReport as $index => $cat)
            @php
                $qty = (float) $cat->total_qty;
                $revenue = (float) $cat->total_revenue;
                $cost = (float) $cat->total_cost;
                $profit = (float) $cat->gross_profit;

                $sumQty += $qty;
                $sumRevenue += $revenue;
                $sumCost += $cost;
                $sumProfit += $profit;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $cat->category_name }}</td>
                <td class="text-center text-muted">{{ $cat->unique_products_count }} Produk</td>
                <td class="text-center font-bold">{{ number_format($qty, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($revenue, 0, ',', '.') }}</td>
                <td class="text-right text-muted">Rp {{ number_format($cost, 0, ',', '.') }}</td>
                <td class="text-right font-bold" style="color: #059669;">Rp {{ number_format($profit, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada transaksi kategori ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse

            @if(count($categoriesReport) > 0)
            <tr class="summary-row">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ number_format($sumQty, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumRevenue, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumCost, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($sumProfit, 0, ',', '.') }}</td>
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
