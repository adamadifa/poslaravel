<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Laba Rugi Sederhana</title>
    <style>
        @page {
            margin: 18px 20px 22px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
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
        .pl-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .pl-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            padding: 7px 6px;
            border: 1px solid #334155;
        }
        .pl-table td {
            padding: 6px 8px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .section-header {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 9.5px;
            color: #0f172a;
            border-top: 1px solid #cbd5e1;
        }
        .sub-row td {
            padding-left: 20px;
            color: #475569;
        }
        .summary-subtotal td {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 9.5px;
            border-top: 1px solid #94a3b8;
            border-bottom: 1px solid #94a3b8;
        }
        .grand-total td {
            background-color: #fff7ed;
            font-weight: bold;
            font-size: 11px;
            border-top: 2px solid #ea580c;
            border-bottom: 3px double #0f172a;
            padding: 8px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .footer-note {
            margin-top: 10px;
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
        <div class="report-title">LAPORAN LABA RUGI SEDERHANA (PROFIT & LOSS)</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Cabang/Gudang: {{ $warehouse->name ?? 'Semua Cabang / Gudang' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="pl-table">
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th width="48%" class="text-left">Komponen Finansial</th>
                <th width="24%" class="text-left">Keterangan</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. PENDAPATAN OPERASIONAL -->
            <tr class="section-header">
                <td class="text-center font-bold">1</td>
                <td class="font-bold">PENDAPATAN PENJUALAN BERSIH</td>
                <td>Gross Sales dikurangi Diskon</td>
                <td class="text-right font-bold">Rp {{ number_format($netSales, 0, ',', '.') }}</td>
            </tr>
            <tr class="sub-row">
                <td></td>
                <td>&bull; Penjualan Kotor (Gross Sales)</td>
                <td>Total subtotal transaksi</td>
                <td class="text-right">Rp {{ number_format($grossSales, 0, ',', '.') }}</td>
            </tr>
            <tr class="sub-row">
                <td></td>
                <td class="text-rose">&bull; Potongan Diskon Penjualan</td>
                <td>Diskon nota & item</td>
                <td class="text-right text-rose">-Rp {{ number_format($salesDiscounts, 0, ',', '.') }}</td>
            </tr>

            <!-- 2. HPP (COGS) -->
            <tr class="section-header">
                <td class="text-center font-bold">2</td>
                <td class="font-bold">BEBAN POKOK PENJUALAN (HPP FIFO)</td>
                <td>Total modal persediaan terjual</td>
                <td class="text-right font-bold text-rose">-Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
            </tr>

            <!-- SUBTOTAL LABA KOTOR -->
            <tr class="summary-subtotal">
                <td></td>
                <td class="font-bold text-emerald">LABA KOTOR (GROSS PROFIT)</td>
                <td>Pendapatan Bersih - Beban HPP</td>
                <td class="text-right font-bold text-emerald">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
            </tr>

            <!-- 3. BIAYA OPERASIONAL -->
            <tr class="section-header">
                <td class="text-center font-bold">3</td>
                <td class="font-bold">BIAYA & BEBAN OPERASIONAL</td>
                <td>Pengeluaran kas operasional</td>
                <td class="text-right font-bold text-rose">-Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
            </tr>
            @forelse($expensesByCategory as $exp)
            <tr class="sub-row">
                <td></td>
                <td>&bull; Beban: {{ ucfirst($exp->category ?: 'Operasional Umum') }}</td>
                <td>Kas Keluar Operasional</td>
                <td class="text-right">-Rp {{ number_format($exp->total_expense, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr class="sub-row">
                <td></td>
                <td colspan="2" style="font-style: italic; color: #94a3b8;">Tidak ada biaya kas keluar tercatat pada periode ini.</td>
                <td class="text-right">Rp 0</td>
            </tr>
            @endforelse

            <!-- 4. LABA BERSIH (NET PROFIT) -->
            <tr class="grand-total">
                <td class="text-center font-bold">4</td>
                <td class="font-bold">LABA BERSIH (NET PROFIT)</td>
                <td style="font-size: 8.5px; font-weight: normal; color: #64748b;">Margin Bersih: {{ number_format($netProfitMargin, 1) }}%</td>
                <td class="text-right font-bold {{ $netProfit >= 0 ? 'text-emerald' : 'text-rose' }}">
                    {{ $netProfit < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($netProfit), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh Sistem POS & Inventori.</div>
        <div class="footer-right">Halaman 1 dari 1</div>
    </div>
</body>
</html>
