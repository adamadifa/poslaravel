<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rekap Shift Kasir</title>
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
        .badge-closed { background-color: #f1f5f9; color: #475569; }
        .badge-active { background-color: #dcfce7; color: #15803d; }
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
        <div class="report-title">LAPORAN REKAPITULASI SESI SHIFT KASIR</div>
        <div class="report-meta">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            &nbsp;|&nbsp; Cabang/Gudang: {{ $warehouse->name ?? 'Semua Cabang' }}
            @if($cashier)
                &nbsp;|&nbsp; Kasir: {{ $cashier->name }}
            @endif
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="13%" class="text-left">Kasir</th>
                <th width="13%" class="text-left">Cabang / Gudang</th>
                <th width="12%" class="text-center">Waktu Buka</th>
                <th width="12%" class="text-center">Waktu Tutup</th>
                <th width="10%" class="text-right">Modal Awal</th>
                <th width="11%" class="text-right">Penjualan Kas</th>
                <th width="10%" class="text-right">Biaya Kasir</th>
                <th width="11%" class="text-right">Fisik Kas Tutup</th>
                <th width="8%" class="text-right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumStart = 0;
                $sumSales = 0;
                $sumExpenses = 0;
                $sumClosing = 0;
                $sumDiff = 0;
            @endphp
            @forelse($shifts as $index => $sh)
            @php
                $sumStart += (float) $sh->starting_cash;
                $sumSales += (float) $sh->total_sales;
                $sumExpenses += (float) ($sh->total_expenses ?? 0);
                if ($sh->closing_cash !== null) {
                    $sumClosing += (float) $sh->closing_cash;
                }
                if ($sh->cash_difference !== null) {
                    $sumDiff += (float) $sh->cash_difference;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $sh->user->name ?? 'Kasir' }}</td>
                <td class="text-left text-muted">{{ $sh->warehouse->name ?? '-' }}</td>
                <td class="text-center">{{ $sh->opened_at ? $sh->opened_at->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-center text-muted">{{ $sh->closed_at ? $sh->closed_at->format('d/m/Y H:i') : 'Aktif' }}</td>
                <td class="text-right">Rp {{ number_format($sh->starting_cash, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($sh->total_sales, 0, ',', '.') }}</td>
                <td class="text-right" style="color: {{ ($sh->total_expenses ?? 0) > 0 ? '#e11d48' : '#64748b' }};">
                    Rp {{ number_format($sh->total_expenses ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-right text-muted">{{ $sh->closing_cash !== null ? 'Rp ' . number_format($sh->closing_cash, 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold" style="color: {{ ($sh->cash_difference ?? 0) < 0 ? '#e11d48' : (($sh->cash_difference ?? 0) > 0 ? '#059669' : '#64748b') }};">
                    @if($sh->cash_difference !== null)
                        {{ $sh->cash_difference < 0 ? '-' : ($sh->cash_difference > 0 ? '+' : '') }}Rp {{ number_format(abs($sh->cash_difference), 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada riwayat sesi shift kasir ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse

            @if(count($shifts) > 0)
            <tr class="summary-row">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($sumStart, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumSales, 0, ',', '.') }}</td>
                <td class="text-right text-danger" style="color: #e11d48;">Rp {{ number_format($sumExpenses, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumClosing, 0, ',', '.') }}</td>
                <td class="text-right" style="color: {{ $sumDiff < 0 ? '#e11d48' : ($sumDiff > 0 ? '#059669' : '#0f172a') }};">
                    {{ $sumDiff < 0 ? '-' : ($sumDiff > 0 ? '+' : '') }}Rp {{ number_format(abs($sumDiff), 0, ',', '.') }}
                </td>
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
